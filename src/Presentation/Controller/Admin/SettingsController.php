<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Settings\UseCase\GetAllSettingsUseCase;
use Rore\Settings\UseCase\SaveSettingsUseCase;

use Rore\Framework\Http\Route;
class SettingsController extends AdminController
{
    public function __construct(
        private readonly GetAllSettingsUseCase   $getAllSettingsUseCase,
        private readonly SaveSettingsUseCase     $saveSettingsUseCase,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/contenu')]
    public function index(): void
    {
        $settings = $this->getAllSettingsUseCase->execute();

        // Grouper par type pour l'affichage
        $texts     = array_filter($settings, fn($s) => $s->getType() === 'text');
        $richtexts = array_filter($settings, fn($s) => $s->getType() === 'richtext');

        $this->render('admin/settings/index', [
            'title'     => 'Contenu & paramètres',
            'texts'     => $texts,
            'richtexts' => $richtexts,
        ]);
    }

    #[Route('POST', '/admin/contenu')]
    public function save(): void
    {
        $this->requirePost();

        $values = $this->request->body->getArray('settings', []);
        $this->saveSettingsUseCase->execute($values);

        $this->flash('success', 'Contenu mis à jour.');
        $this->redirect($this->urlResolver->resolve(self::class . '.index'));
    }
}
