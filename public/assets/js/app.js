// Locarore — JS applicatif minimal

document.addEventListener('DOMContentLoaded', () => {
    // Carousel photos produit
    initCarousel();
    // Confirmation suppression
    initDeleteConfirm();
});

function initCarousel() {
    const carousel = document.getElementById('carousel');
    if (!carousel) return;

    const images = carousel.querySelectorAll('[data-slide]');
    const prevBtn = document.getElementById('carousel-prev');
    const nextBtn = document.getElementById('carousel-next');
    let current = 0;

    function show(index) {
        images.forEach((img, i) => {
            img.classList.toggle('hidden', i !== index);
        });
    }

    show(current);

    prevBtn?.addEventListener('click', () => {
        current = (current - 1 + images.length) % images.length;
        show(current);
    });

    nextBtn?.addEventListener('click', () => {
        current = (current + 1) % images.length;
        show(current);
    });
}

function initDeleteConfirm() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            const msg = el.dataset.confirm || 'Confirmer la suppression ?';
            if (!confirm(msg)) e.preventDefault();
        });
    });
}
