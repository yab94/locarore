<?php

declare(strict_types=1);

namespace Rore\Application\Settings;

interface SettingsServiceInterface
{
    /**
     * Retourne la valeur brute d'une clé de paramètre.
     * Supporte l'interpolation : get('hero.title', ['name' => 'Rore']) remplace {name}.
     *
     * @param array<string,string> $vars
     */
    public function get(string $key, array $vars = []): string;
}
