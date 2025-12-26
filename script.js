document.addEventListener('DOMContentLoaded', function() {
  // Mobile Navigation Toggle
  const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
  const navLinks = document.querySelector('.nav-links');

  mobileNavToggle.addEventListener('click', function() {
    navLinks.classList.toggle('active');
    
    const bars = document.querySelectorAll('.bar');
    bars.forEach(bar => bar.classList.toggle('change'));
    
    const isNavActive = navLinks.classList.contains('active');
    
    if (isNavActive) {
      bars[0].style.transform = 'rotate(-45deg) translate(-5px, 6px)';
      bars[1].style.opacity = '0';
      bars[2].style.transform = 'rotate(45deg) translate(-5px, -6px)';
    } else {
      bars.forEach(bar => {
        bar.style.transform = '';
        bar.style.opacity = '';
      });
    }
  });

  // Close mobile menu when a link is clicked
  const navItems = document.querySelectorAll('.nav-links a');
  navItems.forEach(item => {
    item.addEventListener('click', function() {
      if (navLinks.classList.contains('active')) {
        navLinks.classList.remove('active');
        
        const bars = document.querySelectorAll('.bar');
        bars.forEach(bar => {
          bar.classList.remove('change');
          bar.style.transform = '';
          bar.style.opacity = '';
        });
      }
    });
  });

  // Highlight active navigation item on scroll
  const sections = document.querySelectorAll('section');
  
  window.addEventListener('scroll', function() {
    let currentSection = '';
    
    sections.forEach(section => {
      const sectionTop = section.offsetTop - 100;
      const sectionHeight = section.clientHeight;
      
      if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
        currentSection = section.getAttribute('id');
      }
    });
    
    navItems.forEach(item => {
      item.classList.remove('active');
      if (item.getAttribute('href') === '#' + currentSection) {
        item.classList.add('active');
      }
    });
  });
  
  // Testimonials Slider
  const slides = document.querySelectorAll('.testimonial-slide');
  const dots = document.querySelectorAll('.dot');
  const prevBtn = document.querySelector('.prev-btn');
  const nextBtn = document.querySelector('.next-btn');
  
  let currentSlide = 0;
  const maxSlide = slides.length - 1;
  
  // Initialize dots
  dots.forEach((dot, i) => {
    dot.addEventListener('click', function() {
      goToSlide(i);
    });
  });
  
  // Previous slide button
  prevBtn.addEventListener('click', function() {
    if (currentSlide === 0) {
      currentSlide = maxSlide;
    } else {
      currentSlide--;
    }
    goToSlide(currentSlide);
  });
  
  // Next slide button
  nextBtn.addEventListener('click', function() {
    if (currentSlide === maxSlide) {
      currentSlide = 0;
    } else {
      currentSlide++;
    }
    goToSlide(currentSlide);
  });
  
  // Go to a specific slide
  function goToSlide(slideIndex) {
    slides.forEach((slide, i) => {
      slide.classList.remove('active');
      dots[i].classList.remove('active');
    });
    
    slides[slideIndex].classList.add('active');
    dots[slideIndex].classList.add('active');
    currentSlide = slideIndex;
  }
  
  // Auto slide every 5 seconds
  let slideInterval = setInterval(() => {
    if (currentSlide === maxSlide) {
      currentSlide = 0;
    } else {
      currentSlide++;
    }
    goToSlide(currentSlide);
  }, 5000);
  
  // Pause auto slide on hover
  const testimonialSlider = document.querySelector('.testimonial-slider');
  
  testimonialSlider.addEventListener('mouseenter', function() {
    clearInterval(slideInterval);
  });
  
  testimonialSlider.addEventListener('mouseleave', function() {
    slideInterval = setInterval(() => {
      if (currentSlide === maxSlide) {
        currentSlide = 0;
      } else {
        currentSlide++;
      }
      goToSlide(currentSlide);
    }, 5000);
  });
  
  // Form Submission
  const appointmentForm = document.getElementById('appointment-form');
  
  appointmentForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = {
      name: document.getElementById('name').value,
      email: document.getElementById('email').value,
      phone: document.getElementById('phone').value,
      service: document.getElementById('service').value,
      date: document.getElementById('date').value,
      message: document.getElementById('message').value
    };
    
    // In a real application, you would send this data to a server
    console.log('Form submitted with data:', formData);
    
    // Show success message
    alert('Merci pour votre demande de rendez-vous ! Nous vous contacterons dans les plus brefs délais.');
    
    // Reset form
    appointmentForm.reset();
  });
  
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      
      const target = document.querySelector(this.getAttribute('href'));
      
      if (target) {
        window.scrollTo({
          top: target.offsetTop - 70,
          behavior: 'smooth'
        });
      }
    });
  });
});
