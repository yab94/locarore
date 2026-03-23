<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Security\CsrfTokenManager;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;

class SettingsController extends AdminController
{
    public function __construct(
        private readonly MySqlSettingsRepository $repo,
        SessionStorageInterface                  $session,
        CsrfTokenManager                         $csrfTokenManager,
    ) {
        parent::__construct($session, $csrfTokenManager);
    }

    public function index(): void
    {
        $settings = $this->repo->findAll();

        // Grouper par type pour l'affichage
        $texts     = array_filter($settings, fn($s) => $s->getType() === 'text');
        $richtexts = array_filter($settings, fn($s) => $s->getType() === 'richtext');

        $this->render('admin/settings/index', [
            'title'     => 'Contenu & paramètres',
            'texts'     => $texts,
            'richtexts' => $richtexts,
        ]);
    }

    public function save(): void
    {
        $this->requirePost();

        $values = $this->inputArray('settings', []);
        if (!empty($values)) {
            $this->repo->saveValues($values);
        }

        $this->flash('success', 'Contenu mis à jour.');
        $this->redirect('/admin/contenu');
    }
}
