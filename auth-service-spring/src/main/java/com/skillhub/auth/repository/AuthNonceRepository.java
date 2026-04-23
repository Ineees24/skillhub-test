package com.skillhub.auth.repository;

import com.skillhub.auth.model.AuthNonceEntity;
import java.time.Instant;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;

public interface AuthNonceRepository extends JpaRepository<AuthNonceEntity, Long> {
    Optional<AuthNonceEntity> findByUserIdAndNonce(Long userId, String nonce);
    void deleteByExpiresAtBefore(Instant instant);
}
