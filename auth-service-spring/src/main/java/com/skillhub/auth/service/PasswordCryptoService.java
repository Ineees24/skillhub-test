package com.skillhub.auth.service;

import com.skillhub.auth.config.AuthProperties;
import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.SecureRandom;
import java.util.Base64;
import javax.crypto.Cipher;
import javax.crypto.spec.GCMParameterSpec;
import javax.crypto.spec.SecretKeySpec;
import org.springframework.stereotype.Service;

@Service
public class PasswordCryptoService {
    private static final String TRANSFORMATION = "AES/GCM/NoPadding";
    private static final int IV_LENGTH = 12;
    private static final int TAG_BITS = 128;

    private final SecretKeySpec keySpec;
    private final SecureRandom secureRandom = new SecureRandom();

    public PasswordCryptoService(AuthProperties properties) {
        try {
            byte[] keyBytes = MessageDigest.getInstance("SHA-256")
                    .digest(properties.appMasterKey().getBytes(StandardCharsets.UTF_8));
            this.keySpec = new SecretKeySpec(keyBytes, "AES");
        } catch (Exception e) {
            throw new IllegalStateException("Impossible d’initialiser la clé de chiffrement.", e);
        }
    }

    public String encrypt(String plain) {
        try {
            byte[] iv = new byte[IV_LENGTH];
            secureRandom.nextBytes(iv);
            Cipher cipher = Cipher.getInstance(TRANSFORMATION);
            cipher.init(Cipher.ENCRYPT_MODE, keySpec, new GCMParameterSpec(TAG_BITS, iv));
            byte[] cipherText = cipher.doFinal(plain.getBytes(StandardCharsets.UTF_8));
            return "v1:" + b64(iv) + ":" + b64(cipherText);
        } catch (Exception e) {
            throw new IllegalStateException("Le chriffrement du mot de passe a echoué.", e);
        }
    }

    public String decrypt(String encrypted) {
        try {
            String[] parts = encrypted.split(":");
            if (parts.length != 3 || !"v1".equals(parts[0])) {
                throw new IllegalArgumentException("Format chiffré invalide.");
            }
            byte[] iv = Base64.getDecoder().decode(parts[1]);
            byte[] cipherData = Base64.getDecoder().decode(parts[2]);
            Cipher cipher = Cipher.getInstance(TRANSFORMATION);
            cipher.init(Cipher.DECRYPT_MODE, keySpec, new GCMParameterSpec(TAG_BITS, iv));
            return new String(cipher.doFinal(cipherData), StandardCharsets.UTF_8);
        } catch (Exception e) {
            throw new IllegalStateException("Le déchiffrement du mot de passe a échoué.", e);
        }
    }

    private String b64(byte[] data) {
        return Base64.getEncoder().encodeToString(data);
    }
}
