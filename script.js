document.addEventListener('DOMContentLoaded', (event) => {
    const navLinks = document.querySelectorAll('nav a');
    const sections = document.querySelectorAll('main section');

    function showSection(sectionId) {
        sections.forEach(section => {
            if (section.id === sectionId) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            showSection(targetId);
            // Update URL without reloading the page
            history.pushState(null, '', `#${targetId}`);
        });
    });

    // Show the section based on the current URL hash, or home if no hash
    const initialSection = window.location.hash.substring(1) || 'home';
    showSection(initialSection);

    // Handle browser back/forward navigation
    window.addEventListener('popstate', () => {
        const currentSection = window.location.hash.substring(1) || 'home';
        showSection(currentSection);
    });
});