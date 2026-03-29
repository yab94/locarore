CREATE TABLE faq_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question   VARCHAR(500)  NOT NULL,
    answer     TEXT          NOT NULL,
    position   SMALLINT      NOT NULL DEFAULT 0,
    is_visible TINYINT(1)    NOT NULL DEFAULT 1,
    created_at DATETIME      NOT NULL DEFAULT NOW(),
    updated_at DATETIME      NOT NULL DEFAULT NOW() ON UPDATE NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
