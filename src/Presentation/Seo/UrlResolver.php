<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

/**
 * Résout les URLs canoniques des entités du catalogue.
 * Instance injectable via DI — prend Config en constructeur.
 */
final class UrlResolver extends \Rore\Framework\UrlResolver
{
