document.addEventListener('DOMContentLoaded', () => {
  // Fetch navbar
  fetch('navbar.html')
    .then(response => response.text())
    .then(data => {
      document.body.insertAdjacentHTML('afterbegin', data);

      // Mobile menu toggle
      document.querySelector('.mobile-menu-btn')?.addEventListener('click', () => {
        document.querySelector('.nav-links')?.classList.toggle('active');
      });

      // Smooth scroll
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelector(this.getAttribute('href'))?.scrollIntoView({
            behavior: 'smooth'
          });
        });
      });
    });
  // Fetch footer
  fetch('footer.html')
    .then(response => response.text())
    .then(data => {
      document.body.insertAdjacentHTML('beforeend', data);
    });

  // Intersection Observer for animations
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.feature-card, .member-card').forEach((el) => {
    observer.observe(el);
  });
});

// Join function
// function join() {
//   alert("Coming Soon! Stay Tuned!");
// }
// const menuBtn = document.querySelector('.mobile-menu-btn');
//     const navLinks = document.querySelector('.nav-links');
//     const darkModeToggle = document.getElementById('darkModeToggle');
//     const body = document.body;

//     menuBtn.addEventListener('click', () => {
//       navLinks.classList.toggle('active');
//     });

//     // Toggle Dark Mode
//     darkModeToggle.addEventListener('click', () => {
//       body.classList.toggle('dark-mode');
//       const isDarkMode = body.classList.contains('dark-mode');
//       darkModeToggle.innerHTML = isDarkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
//     });
  // Load testimonial content into the section

  // fetch('testi.html') // Make sure the path is correct
  //   .then(response => response.text())
  //   .then(html => {
  //     document.getElementById('testimonial-section').innerHTML = html;
  //   })
  //   .catch(err => {
  //     console.error('Error loading testimonial section:', err);
  //   });
