-- Settings pour le formulaire de contact
-- À exécuter via : make db-schema (ou mysql directement)

INSERT IGNORE INTO `settings` (`key`, `value`, `label`, `type`, `group`) VALUES
('contact.page_title',    'Contactez-nous',                        'Contact — titre de la page',           'text', 'contact'),
('contact.page_intro',    'Une question ? Un projet ? Écrivez-nous.', 'Contact — texte d\'introduction',   'text', 'contact'),
('contact.email_to',      '',                                       'Contact — email de destination (admin)', 'text', 'contact'),
('contact.subject_prefix','Contact',                                'Contact — préfixe objet du mail',      'text', 'contact');

CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(100)  NOT NULL,
    last_name   VARCHAR(100)  NOT NULL,
    email       VARCHAR(255)  NOT NULL,
    phone       VARCHAR(30)   NULL,
    subject     VARCHAR(255)  NOT NULL,
    content     TEXT          NOT NULL,
    is_read     TINYINT(1)    NOT NULL DEFAULT 0,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
