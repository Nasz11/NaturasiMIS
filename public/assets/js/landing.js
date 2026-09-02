/* =========================================================
   NaturasiMIS - Landing Page JavaScript
   Smooth scrolling, animations, and interactive elements
   ========================================================= */

document.addEventListener("DOMContentLoaded", () => {

  /* =========================
     Sticky Header on Scroll
     ========================= */
  const header = document.querySelector("header");
  window.addEventListener("scroll", () => {
    header.classList.toggle("sticky", window.scrollY > 50);
  });

  /* =========================
     Smooth Scroll for Links
     ========================= */
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener("click", e => {
      const target = document.querySelector(link.getAttribute("href"));
      if (target) {
        e.preventDefault();
        window.scrollTo({
          top: target.offsetTop - 70,
          behavior: "smooth"
        });
        
        // Close mobile menu if open
        const navMenu = document.querySelector(".nav-menu");
        if (navMenu.classList.contains("active")) {
          navMenu.classList.remove("active");
          document.querySelector(".hamburger").classList.remove("open");
        }
      }
    });
  });

  /* =========================
     Scroll Reveal Animations
     ========================= */
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, { threshold: 0.15 });

  document.querySelectorAll(".feature-card, .section-title, .section-subtitle").forEach(el => {
    el.style.opacity = "0";
    el.style.transform = "translateY(30px)";
    el.style.transition = "all 0.6s ease";
    observer.observe(el);
  });

  /* =========================
     Hero Buttons
     ========================= */
  const getStartedBtn = document.querySelector(".get-started-btn");
  const learnMoreBtn = document.querySelector(".learn-more-btn");

  if (getStartedBtn) {
    getStartedBtn.addEventListener("click", e => {
      e.preventDefault();
      window.location.href = "/login";
    });
  }

  if (learnMoreBtn) {
    learnMoreBtn.addEventListener("click", e => {
      e.preventDefault();
      const aboutSection = document.querySelector("#about");
      if (aboutSection) {
        aboutSection.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  }

  /* =========================
     Back to Top Button
     ========================= */
  const backToTop = document.querySelector(".back-to-top");
  
  window.addEventListener("scroll", () => {
    if (window.scrollY > 600) {
      backToTop.classList.add("show");
    } else {
      backToTop.classList.remove("show");
    }
  });

  backToTop?.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  /* =========================
     Mobile Menu Toggle
     ========================= */
  const hamburger = document.querySelector(".hamburger");
  const navMenu = document.querySelector(".nav-menu");

  if (hamburger && navMenu) {
    hamburger.addEventListener("click", () => {
      navMenu.classList.toggle("active");
      hamburger.classList.toggle("open");
      
      // Change icon
      const icon = hamburger.querySelector("i");
      if (navMenu.classList.contains("active")) {
        icon.className = "ri-close-line";
      } else {
        icon.className = "ri-menu-line";
      }
    });
    
    // Close menu when clicking outside
    document.addEventListener("click", (e) => {
      if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
        navMenu.classList.remove("active");
        hamburger.classList.remove("open");
        hamburger.querySelector("i").className = "ri-menu-line";
      }
    });
  }

  /* =========================
     Active Section Highlight
     ========================= */
  const sections = document.querySelectorAll("section");
  const navLinks = document.querySelectorAll(".nav-link");

  const highlightNav = () => {
    let current = "";
    sections.forEach(section => {
      const sectionTop = section.offsetTop - 100;
      if (scrollY >= sectionTop) current = section.id;
    });

    navLinks.forEach(link => {
      link.classList.remove("active");
      if (link.getAttribute("href") === `#${current}`) {
        link.classList.add("active");
      }
    });
  };

  window.addEventListener("scroll", highlightNav);

  /* =========================
     Hero Fade-In
     ========================= */
  const hero = document.querySelector(".hero");
  if (hero) {
    hero.style.opacity = "0";
    setTimeout(() => {
      hero.style.transition = "opacity 1s ease";
      hero.style.opacity = "1";
    }, 100);
  }

  /* =========================
     Feature Cards Hover Effect
     ========================= */
  document.querySelectorAll(".feature-card").forEach(card => {
    card.addEventListener("mouseenter", function() {
      this.style.borderColor = "var(--primary)";
    });
    
    card.addEventListener("mouseleave", function() {
      this.style.borderColor = "transparent";
    });
  });

  /* =========================
     Performance: Debounce Scroll
     ========================= */
  let scrollTimer;
  window.addEventListener("scroll", () => {
    if (scrollTimer) {
      window.cancelAnimationFrame(scrollTimer);
    }
    
    scrollTimer = window.requestAnimationFrame(() => {
      highlightNav();
    });
  });
});