document.addEventListener('DOMContentLoaded', () => {
  // Show/Hide Menu
  const toggle = document.querySelector('.nav__toggle');
  const navbar = document.querySelector('.l-navbar');

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

  // Handle section navigation
  const navLinks = document.querySelectorAll('.nav__link[data-section]');
  const sections = document.querySelectorAll('.section');

  const showSection = (sectionId) => {
    sections.forEach(section => section.classList.remove('active'));
    const target = document.getElementById(sectionId);
    if (target) target.classList.add('active');
  };

  // Show dashboard overview by default
  showSection('dashboard-overview');

  navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      navLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      showSection(link.getAttribute('data-section'));
    });
  });

  const dashboardLink = document.querySelector('.nav__link[data-section="dashboard-overview"]');
  if (dashboardLink) dashboardLink.classList.add('active');
});

// Modal functions
function openReviewModal(productId, productName) {
  const modal = document.getElementById('reviewModal');
  if (modal) {
    modal.style.display = 'block';
    document.getElementById('product_id').value = productId;
    document.getElementById('productName').textContent = 'Product: ' + productName;
  }
}

function closeReviewModal() {
  const modal = document.getElementById('reviewModal');
  if (modal) {
    modal.style.display = 'none';
  }
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('reviewModal');
  if (modal && event.target === modal) {
    closeReviewModal();
  }
};