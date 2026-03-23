// Show/Hide Menu
const showMenu = (toggleId, navbarId, bodyId) => {
    const toggle = document.getElementById(toggleId),
          navbar = document.getElementById(navbarId),
          bodypadding = document.getElementById(bodyId),
          mainContent = document.querySelector('main');

    if (toggle && navbar) {
        toggle.addEventListener('click', () => {
            navbar.classList.toggle('show');
            toggle.classList.toggle('rotate');
            bodypadding.classList.toggle('expander');
            mainContent.style.paddingLeft = navbar.classList.contains('show') ? '188px' : '76px';
        });
    }
}

showMenu('nav-toggle', 'navbar', 'body');

// Handle section navigation
document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav__link[data-section]');
    const sections = document.querySelectorAll('.section');

    // Check URL param or hash for initial section
    const urlParams = new URLSearchParams(window.location.search);
    const initSection = urlParams.get('section') || window.location.hash.slice(1);
    if (initSection) {
        const target = document.getElementById(initSection);
        if (target) {
            sections.forEach(s => s.classList.remove('active'));
            target.classList.add('active');
            navLinks.forEach(l => {
                l.classList.toggle('active', l.getAttribute('data-section') === initSection);
            });
        }
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            sections.forEach(s => s.classList.remove('active'));

            const sectionId = link.getAttribute('data-section');
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });
});

// Review modal functions
function openReviewModal(productId, productName) {
    document.getElementById('reviewModal').style.display = 'flex';
    document.getElementById('product_id').value = productId;
    document.getElementById('productName').textContent = 'Product: ' + productName;
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

function viewReview(reviewId) {
    window.location.href = 'view_review.php?id=' + reviewId;
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == document.getElementById('reviewModal')) {
        closeReviewModal();
    }
}
