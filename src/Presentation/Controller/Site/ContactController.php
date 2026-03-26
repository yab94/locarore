<?php

declare(strict_types=1);

namespace Rore\Presentation\Controller\Site;

use Rore\Application\Contact\SendContactMessageUseCase;
use Rore\Framework\Route;

final class ContactController extends SiteController
{
    public function __construct(
        private readonly SendContactMessageUseCase $sendContactMessage,
        ...$parentDeps
    ) {
        parent::__construct(...$parentDeps);
    }

    #[Route('GET', '/contact')]
    public function index(): void
    {
        $this->render('site/contact', [
            'meta' => (new \Rore\Framework\PageMeta(
                title: $this->settings->get('contact.page_title') ?: 'Contact',
            )),
        ]);
    }

    #[Route('POST', '/contact')]
    public function send(): void
    {
        $this->requirePost();

        $firstName = trim($this->request->body->getString('first_name'));
        $lastName  = trim($this->request->body->getString('last_name'));
        $email     = trim($this->request->body->getString('email'));
        $phone     = trim($this->request->body->getString('phone')) ?: null;
        $subject   = trim($this->request->body->getString('subject'));
        $content   = trim($this->request->body->getString('content'));

        if ($firstName === '' || $lastName === '' || $email === '' || $subject === '' || $content === '') {
            $this->flash('error', 'Veuillez remplir tous les champs obligatoires.');
            $this->redirect($this->urlResolver->resolve('Site\Contact.index'));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'L\'adresse email n\'est pas valide.');
            $this->redirect($this->urlResolver->resolve('Site\Contact.index'));
        }

        try {
            $this->sendContactMessage->execute(
                firstName: $firstName,
                lastName:  $lastName,
                email:     $email,
                phone:     $phone,
                subject:   $subject,
                content:   $content,
            );
            $this->redirect($this->urlResolver->resolve('Site\Contact.confirmation'));
        } catch (\Throwable) {
            $this->flash('error', 'Une erreur est survenue. Veuillez réessayer.');
            $this->redirect($this->urlResolver->resolve('Site\Contact.index'));
        }
    }

    #[Route('GET', '/contact/merci')]
    public function confirmation(): void
    {
        $this->render('site/contact-confirmation', [
            'meta' => (new \Rore\Framework\PageMeta(
                title: 'Message envoyé',
                robots: 'noindex, follow',
            )),
        ]);
    }
}
