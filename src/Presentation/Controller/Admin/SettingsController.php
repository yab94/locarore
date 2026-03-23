<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Security\CsrfTokenManagerInterface;
use Rore\Application\Settings\SettingsServiceInterface;
use Rore\Application\Storage\SessionStorageInterface;
use Rore\Infrastructure\Config\Config;
use Rore\Infrastructure\Persistence\MySqlSettingsRepository;
use Rore\Presentation\Http\RequestInterface;
use Rore\Presentation\Http\ResponseInterface;

class SettingsController extends AdminController
{
    public function __construct(
        private readonly MySqlSettingsRepository $repo,
        RequestInterface                         $request,
        ResponseInterface                        $response,
        Config                                   $config,
        SessionStorageInterface                  $session,
        CsrfTokenManagerInterface                $csrfTokenManager,
        SettingsServiceInterface                            $settings,
    ) {
        parent::__construct($request, $response, $config, $session, $csrfTokenManager, $settings);
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

        $values = $this->request->inputArray('settings', []);
        if (!empty($values)) {
            $this->repo->saveValues($values);
        }

        $this->flash('success', 'Contenu mis à jour.');
        $this->redirect('/admin/contenu');
    }
}
