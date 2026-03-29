<?php

declare(strict_types=1);

namespace RRB\Security;

use RRB\Session\SessionInterface;

/**
 * Limiteur de tentatives générique basé sur la session.
 *
 * Les seuils et la clé sont fixés en constructeur.
 * La session ne stocke que l'état pur : attempts + locked_until.
 *
 * Usage (injection via bindParameter) :
 *   $container->bindParameter(LoginController::class, 'rateLimiter',
 *       fn(SessionInterface $session) => [$session, 'admin_login', 5, 900]
 *   );
 *   private readonly RateLimiter $rateLimiter,
 */
class RateLimiter
{
    private const PREFIX = 'rate_limit.';

    public function __construct(
        private readonly SessionInterface $session,
        private readonly string $key,
        private readonly int    $maxAttempts    = 5,
        private readonly int    $lockoutSeconds = 900,
    ) {}

    /**
     * Enregistre une tentative échouée et pose un verrou si le seuil est atteint.
     */
    public function hit(): void
    {
        $state = $this->loadState();
        $state['attempts']++;

        if ($state['attempts'] >= $this->maxAttempts) {
            $state['locked_until'] = time() + $this->lockoutSeconds;
        }

        $this->saveState($state);
    }

    /**
     * Vérifie si la clé est actuellement verrouillée.
     */
    public function isLocked(): bool
    {
        $state = $this->loadState();
        if ($state['locked_until'] === 0) {
            return false;
        }
        if (time() >= $state['locked_until']) {
            $this->reset();
            return false;
        }
        return true;
    }

    /**
     * Retourne le nombre de secondes restantes avant déverrouillage.
     */
    public function secondsUntilUnlock(): int
    {
        $state = $this->loadState();
        if ($state['locked_until'] === 0) {
            return 0;
        }
        return max(0, $state['locked_until'] - time());
    }

    /**
     * Réinitialise le compteur (ex. après succès).
     */
    public function reset(): void
    {
        $this->session->remove(self::PREFIX . $this->key);
    }

    // -------------------------------------------------------------------------

    private function loadState(): array
    {
        $data = $this->session->get(self::PREFIX . $this->key, null);
        if (!is_array($data)) {
            return ['attempts' => 0, 'locked_until' => 0];
        }
        return [
            'attempts'     => (int) ($data['attempts']     ?? 0),
            'locked_until' => (int) ($data['locked_until'] ?? 0),
        ];
    }

    private function saveState(array $state): void
    {
        $this->session->set(self::PREFIX . $this->key, $state);
    }
}
