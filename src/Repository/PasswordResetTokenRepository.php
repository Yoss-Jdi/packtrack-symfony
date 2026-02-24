<?php

namespace App\Repository;

use App\Entity\PasswordResetToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    /**
     * Trouve un token valide (non utilisé et non expiré)
     */
    public function findValidToken(string $token): ?PasswordResetToken
    {
        $resetToken = $this->findOneBy(['token' => $token]);

        if (!$resetToken) {
            return null;
        }

        // Vérifier si le token est valide
        if ($resetToken->isValid()) {
            return $resetToken;
        }

        return null;
    }

    /**
     * Supprime tous les anciens tokens pour un email donné
     */
    public function deleteOldTokensForEmail(string $email): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->execute();
    }

    /**
     * Supprime tous les tokens expirés
     */
    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }

    /**
     * Supprime tous les tokens utilisés de plus de 24h
     */
    public function deleteUsedTokens(): int
    {
        $yesterday = new \DateTime('-1 day');
        
        return $this->createQueryBuilder('t')
            ->delete()
            ->where('t.used = :used')
            ->andWhere('t.createdAt < :yesterday')
            ->setParameter('used', true)
            ->setParameter('yesterday', $yesterday)
            ->getQuery()
            ->execute();
    }

    /**
     * Compte les tokens actifs pour un email
     */
    public function countActiveTokensForEmail(string $email): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.email = :email')
            ->andWhere('t.used = :used')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('email', $email)
            ->setParameter('used', false)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nettoie la base de données (tokens expirés + utilisés anciens)
     */
    public function cleanupDatabase(): array
    {
        $expiredCount = $this->deleteExpiredTokens();
        $usedCount = $this->deleteUsedTokens();

        return [
            'expired' => $expiredCount,
            'used' => $usedCount,
            'total' => $expiredCount + $usedCount
        ];
    }

    // Dans PasswordResetTokenRepository.php, ajouter cette méthode
/**
 * Trouve un token valide par email et code
 */
public function findValidTokenByEmailAndCode(string $email, string $code): ?PasswordResetToken
{
    $qb = $this->createQueryBuilder('t');
    
    $resetToken = $qb
        ->where('t.email = :email')
        ->andWhere('t.token = :token')
        ->setParameter('email', $email)
        ->setParameter('token', $code)
        ->getQuery()
        ->getOneOrNullResult();

    if (!$resetToken) {
        return null;
    }

    // Vérifier si le token est valide
    if ($resetToken->isValid()) {
        return $resetToken;
    }

    return null;
}
}
