package com.skillhub.auth.controller;

import com.skillhub.auth.dto.AuthDtos;
import com.skillhub.auth.service.AuthService;
import jakarta.validation.Valid;
import java.util.Map;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.server.ResponseStatusException;

@RestController
@RequestMapping("/api/auth")
public class AuthController {
    private final AuthService authService;

    public AuthController(AuthService authService) {
        this.authService = authService;
    }

    @PostMapping("/register")
    @ResponseStatus(HttpStatus.CREATED)
    public Map<String, Object> register(@Valid @RequestBody AuthDtos.RegisterRequest request) {
        return Map.of(
                "message", "User created. Use SSO login endpoint.",
                "user", authService.register(request)
        );
    }

    @PostMapping("/login")
    public AuthDtos.TokenResponse login(@Valid @RequestBody AuthDtos.LoginRequest request) {
        return authService.login(request);
    }

    @PostMapping("/introspect")
    public Map<String, Object> introspect(@Valid @RequestBody AuthDtos.IntrospectRequest request) {
        return Map.of("active", authService.introspect(request.token()));
    }

    @GetMapping("/me")
    public AuthDtos.UserView me(@RequestHeader(value = "Authorization", required = false) String authorization) {
        String token = extractBearer(authorization);
        return authService.me(token);
    }

    @PostMapping("/logout")
    public Map<String, String> logout(@RequestHeader(value = "Authorization", required = false) String authorization) {
        String token = extractBearer(authorization);
        authService.logout(token);
        return Map.of("message", "Logout successful.");
    }

    private String extractBearer(String authorization) {
        if (authorization == null || !authorization.startsWith("Bearer ")) {
            throw new ResponseStatusException(HttpStatus.UNAUTHORIZED, "Missing bearer token.");
        }
        return authorization.substring(7);
    }
}
