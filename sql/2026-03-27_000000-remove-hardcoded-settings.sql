-- Migration : suppression des settings désormais codés en dur dans les templates

DELETE FROM `settings`
WHERE `key` IN (
    'cart.footer_note',
    'confirmation.title',
    'confirmation.message',
    'reservation.status.label.pending',
    'reservation.status.label.quoted',
    'reservation.status.label.confirmed',
    'reservation.status.label.cancelled',
    'reservation.status.filter.all',
    'reservation.status.filter.pending',
    'reservation.status.filter.quoted',
    'reservation.status.filter.confirmed',
    'reservation.status.filter.cancelled',
    'site.name',
    'site.tagline',
    'hero.title',
    'hero.subtitle',
    'hero.cta',
    'home.categories_title',
    'home.featured_title'
);
