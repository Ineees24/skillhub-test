package com.skillhub.auth.dto;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Pattern;
import jakarta.validation.constraints.Size;

public class AuthDtos {
    public record RegisterRequest(
            @NotBlank @Email String email,
            @NotBlank @Pattern(regexp = "APPRENANT|FORMATEUR|ADMINISTRATEUR") String role,
            @NotBlank @Size(min = 12) String password
    ) {}

    public record LoginRequest(
            @NotBlank @Email String email,
            @NotBlank @Size(min = 16, max = 120) String nonce,
            @NotNull Long timestamp,
            @NotBlank @Size(min = 64, max = 64) String hmac
    ) {}

    public record IntrospectRequest(@NotBlank String token) {}
    public record TokenResponse(String accessToken, String tokenType, String expiresAt, UserView user) {}
    public record UserView(Long id, String email, String role) {}
}
