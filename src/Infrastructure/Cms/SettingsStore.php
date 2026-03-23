<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Cms;

use Rore\Infrastructure\Persistence\MySqlSettingsRepository;

/**
 * Façade statique d'accès aux paramètres CMS.
 * Conserve une instance unique du repository (pattern identique à l'ancien helper).
 */
final class SettingsStore
{
    private static ?MySqlSettingsRepository $repo = null;

    /**
     * Retourne la valeur brute d'une clé de paramètre.
     * Supporte l'interpolation : get('hero.title', ['name' => 'Rore']) remplace {name}.
     *
     * @param array<string,string> $vars
     */
    public static function get(string $key, array $vars = []): string
    {
        if (self::$repo === null) {
            self::$repo = new MySqlSettingsRepository();
        }
        $setting = self::$repo->findByKey($key);
        $value   = $setting?->getValue() ?? '';

        foreach ($vars as $k => $v) {
            $value = str_replace('{' . $k . '}', (string) $v, $value);
        }
        return $value;
    }

    /**
     * Retourne la valeur échappée pour l'affichage HTML.
     *
     * @param array<string,string> $vars
     */
    public static function html(string $key, array $vars = []): string
    {
        return htmlspecialchars(self::get($key, $vars), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
