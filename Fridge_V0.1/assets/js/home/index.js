function initSplide() {
    const el = document.getElementById('recipe-carousel');
    if (!el || el.classList.contains('is-initialized')) return;

    new Splide('#recipe-carousel', {
        type: 'loop',
        perPage: 3,
        perMove: 1,
        gap: '1.5rem',
        breakpoints: {
            992: { perPage: 2 },
            576: { perPage: 1 },
        },
    }).mount();
}

document.addEventListener('turbo:load', initSplide);
document.addEventListener('turbo:render', initSplide);
