package com.skillhub.auth.service;

import com.skillhub.auth.config.AuthProperties;
import com.skillhub.auth.dto.AuthDtos;
import com.skillhub.auth.model.AuthNonceEntity;
import com.skillhub.auth.model.AuthTokenEntity;
import com.skillhub.auth.model.UserEntity;
import com.skillhub.auth.repository.AuthNonceRepository;
import com.skillhub.auth.repository.AuthTokenRepository;
import com.skillhub.auth.repository.UserRepository;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.SecureRandom;
import java.time.Instant;
import java.util.HexFormat;
import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

@Service
public class AuthService {
    private final UserRepository userRepository;
    private final AuthNonceRepository nonceRepository;
    private final AuthTokenRepository tokenRepository;
    private final PasswordCryptoService passwordCryptoService;
    private final AuthProperties properties;
    private final SecureRandom secureRandom = new SecureRandom();

    public AuthService(UserRepository userRepository, AuthNonceRepository nonceRepository, AuthTokenRepository tokenRepository,
                       PasswordCryptoService passwordCryptoService, AuthProperties properties) {
        this.userRepository = userRepository;
        this.nonceRepository = nonceRepository;
        this.tokenRepository = tokenRepository;
        this.passwordCryptoService = passwordCryptoService;
        this.properties = properties;
    }

    public AuthDtos.UserView register(AuthDtos.RegisterRequest request) {
        userRepository.findByEmail(request.email()).ifPresent(u -> {
            throw new ResponseStatusException(HttpStatus.CONFLICT, "Email existe deja.");
        });

        UserEntity user = new UserEntity();
        user.setEmail(request.email().toLowerCase());
        user.setRole(request.role());
        user.setPasswordEncrypted(passwordCryptoService.encrypt(request.password()));
        UserEntity saved = userRepository.save(user);
        return toView(saved);
    }

    @Transactional
    public AuthDtos.TokenResponse login(AuthDtos.LoginRequest request) {
        UserEntity user = userRepository.findByEmail(request.email().toLowerCase())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Invalid credentials."));

        validateTimestamp(request.timestamp());
        nonceRepository.deleteByExpiresAtBefore(Instant.now());
        nonceRepository.findByUserIdAndNonce(user.getId(), request.nonce()).ifPresent(existing -> {
            throw new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Nonce deja utilisé.");
        });

        AuthNonceEntity nonce = new AuthNonceEntity();
        nonce.setUser(user);
        nonce.setNonce(request.nonce());
        nonce.setConsumed(false);
        nonce.setExpiresAt(Instant.now().plusSeconds(properties.nonceTtlSeconds()));
        nonceRepository.save(nonce);

        String passwordPlain = passwordCryptoService.decrypt(user.getPasswordEncrypted());
        String message = request.email().toLowerCase() + ":" + request.nonce() + ":" + request.timestamp();
        String expected = hmacSha256(passwordPlain, message);

        if (!MessageDigest.isEqual(expected.getBytes(StandardCharsets.UTF_8), request.hmac().toLowerCase().getBytes(StandardCharsets.UTF_8))) {
            throw new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Invalid credentials.");
        }

        nonce.setConsumed(true);
        String rawToken = generateToken();
        AuthTokenEntity token = new AuthTokenEntity();
        token.setUser(user);
        token.setTokenHash(sha256(rawToken));
        token.setExpiresAt(Instant.now().plusSeconds(properties.tokenTtlSeconds()));
        tokenRepository.save(token);

        return new AuthDtos.TokenResponse(rawToken, "bearer", token.getExpiresAt().toString(), toView(user));
    }

    public void logout(String rawToken) {
        resolveByToken(rawToken).ifPresent(token -> {
            token.setRevokedAt(Instant.now());
            tokenRepository.save(token);
        });
    }

    public AuthDtos.UserView me(String rawToken) {
        AuthTokenEntity token = resolveByToken(rawToken)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Token invalid."));
        return toView(token.getUser());
    }

    public boolean introspect(String rawToken) {
        return resolveByToken(rawToken).isPresent();
    }

    public java.util.Optional<AuthTokenEntity> resolveByToken(String rawToken) {
        if (rawToken == null || rawToken.isBlank()) {
            return java.util.Optional.empty();
        }
        return tokenRepository.findByTokenHashAndRevokedAtIsNullAndExpiresAtAfter(sha256(rawToken), Instant.now());
    }

    private void validateTimestamp(Long timestamp) {
        long now = Instant.now().getEpochSecond();
        if (Math.abs(now - timestamp) > properties.timestampToleranceSeconds()) {
            throw new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Timestamp non autorisé.");
        }
    }

    private AuthDtos.UserView toView(UserEntity user) {
        return new AuthDtos.UserView(user.getId(), user.getEmail(), user.getRole());
    }

    private String generateToken() {
        byte[] data = new byte[48];
        secureRandom.nextBytes(data);
        return HexFormat.of().formatHex(data);
    }

    private String sha256(String value) {
        try {
            return HexFormat.of().formatHex(MessageDigest.getInstance("SHA-256")
                    .digest(value.getBytes(StandardCharsets.UTF_8)));
        } catch (Exception e) {
            throw new IllegalStateException("Hashing failed.", e);
        }
    }

    private String hmacSha256(String key, String message) {
        try {
            Mac mac = Mac.getInstance("HmacSHA256");
            mac.init(new SecretKeySpec(key.getBytes(StandardCharsets.UTF_8), "HmacSHA256"));
            return HexFormat.of().formatHex(mac.doFinal(message.getBytes(StandardCharsets.UTF_8)));
        } catch (Exception e) {
            throw new IllegalStateException("HMAC failed.", e);
        }
    }
}
