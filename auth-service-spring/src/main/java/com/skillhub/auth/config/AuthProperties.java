package com.skillhub.auth.config;

import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotBlank;
import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.validation.annotation.Validated;

@Validated
@ConfigurationProperties(prefix = "skillhub.auth")
public record AuthProperties(
        @NotBlank String appMasterKey,
        @Min(1) long timestampToleranceSeconds,
        @Min(10) long nonceTtlSeconds,
        @Min(60) long tokenTtlSeconds
) {
}
