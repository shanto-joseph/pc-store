document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('nav-toggle');
    const navbar = document.getElementById('navbar');

    if (toggle && navbar) {
        toggle.addEventListener('click', () => {
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                navbar.classList.toggle('show');
            } else {
                navbar.classList.toggle('show');
                toggle.classList.toggle('rotate');
                document.body.classList.toggle('expander');
            }
        });
    }

    // Section navigation
    const navLinks = document.querySelectorAll('.nav__link[data-section]');
    const sections = document.querySelectorAll('.section');

    const showSection = (id) => {
        sections.forEach(s => s.classList.remove('active'));
        const target = document.getElementById(id);
        if (target) target.classList.add('active');
        navLinks.forEach(l => l.classList.remove('active'));
        const activeLink = document.querySelector(`.nav__link[data-section="${id}"]`);
        if (activeLink) activeLink.classList.add('active');
    };

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            showSection(link.getAttribute('data-section'));
        });
    });

    // Alert box "View Products" link
    document.querySelectorAll('.alert-link[data-section]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            showSection(link.getAttribute('data-section'));
        });
    });

    // Show first section by default
    const first = document.querySelector('.section');
    if (first) first.classList.add('active');
});

// Product filter & search
function filterProducts(status) {
    document.querySelectorAll('.vendor-product-card').forEach(card => {
        if (status === 'all') {
            card.style.display = '';
        } else {
            card.style.display = card.dataset.status === status ? '' : 'none';
        }
    });
}

function searchProducts(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.vendor-product-card').forEach(card => {
        const text = card.querySelector('h3').textContent.toLowerCase();
        card.style.display = text.includes(query) ? '' : 'none';
    });
}
