<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Admin;

use Rore\Application\Contact\UseCase\DeleteContactMessageUseCase;
use Rore\Application\Contact\UseCase\GetContactMessageUseCase;
use Rore\Application\Contact\UseCase\GetContactMessagesUseCase;
use Rore\Application\Contact\UseCase\MarkMessageReadUseCase;
use Rore\Application\Contact\UseCase\MarkMessageUnreadUseCase;
use Rore\Framework\Http\Route;

final class MessageController extends AdminController
{
    public function __construct(
        private readonly GetContactMessagesUseCase $getMessages,
        private readonly GetContactMessageUseCase  $getMessage,
        private readonly MarkMessageReadUseCase    $markRead,
        private readonly MarkMessageUnreadUseCase  $markUnread,
        private readonly DeleteContactMessageUseCase $deleteMessage,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/admin/messages')]
    public function index(): void
    {
        $messages = $this->getMessages->all();

        $this->render('admin/messages/list', [
            'title'    => 'Messages',
            'messages' => $messages,
        ]);
    }

    #[Route('GET', '/admin/messages/{id}')]
    public function show(string $id): void
    {
        $id      = (int) $id;
        $message = $this->getMessage->execute($id);

        // Marquer automatiquement comme lu à l'ouverture
        if (!$message->isRead()) {
            $this->markRead->execute($id);
        }

        $this->render('admin/messages/show', [
            'title'   => 'Message de ' . $message->getFullName(),
            'message' => $message,
        ]);
    }

    #[Route('POST', '/admin/messages/{id}/lire')]
    public function markRead(string $id): void
    {
        $this->requirePost();
        $this->markRead->execute((int) $id);
        $this->flash('success', 'Message marqué comme lu.');
        $this->redirect($this->urlResolver->resolve('Admin\Message.index'));
    }

    #[Route('POST', '/admin/messages/{id}/non-lu')]
    public function markUnread(string $id): void
    {
        $this->requirePost();
        $this->markUnread->execute((int) $id);
        $this->flash('success', 'Message marqué comme non lu.');
        $this->redirect($this->urlResolver->resolve('Admin\Message.index'));
    }

    #[Route('POST', '/admin/messages/{id}/supprimer')]
    public function delete(string $id): void
    {
        $this->requirePost();
        $this->deleteMessage->execute((int) $id);
        $this->flash('success', 'Message supprimé.');
        $this->redirect($this->urlResolver->resolve('Admin\Message.index'));
    }
}
