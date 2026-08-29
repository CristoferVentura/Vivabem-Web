(() => {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.addEventListener('DOMContentLoaded', () => {
    if (prefersReduced) return;

    document.querySelectorAll('.slider-container').forEach((slider) => {
      const slides = slider.querySelectorAll('.slide');
      if (slides.length <= 1) return;

      let currentIndex = 0;

      setInterval(() => {
        slides[currentIndex].classList.remove('active');
        currentIndex = (currentIndex + 1) % slides.length;
        slides[currentIndex].classList.add('active');
      }, 3500);
    });
  });

  // Mobile nav
  const toggle = document.querySelector('.nav-toggle');
  const menu = document.querySelector('.nav-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      const open = menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Flash dismiss (manual + auto after 10s)
  const dismissFlash = (flash) => {
    if (!flash || flash.classList.contains('is-hiding')) return;
    flash.classList.add('is-hiding');
    window.setTimeout(() => flash.remove(), 400);
  };

  document.querySelectorAll('.flash').forEach((flash) => {
    const closeBtn = flash.querySelector('.flash-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => dismissFlash(flash));
    }
    window.setTimeout(() => dismissFlash(flash), 10000);
  });

  // Form field feedback
  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      let valid = true;
      form.querySelectorAll('[required]').forEach((field) => {
        const empty = !String(field.value || '').trim();
        field.classList.toggle('is-invalid', empty);
        if (empty) valid = false;
      });
      if (!valid) event.preventDefault();
    });

    form.querySelectorAll('input, textarea, select').forEach((field) => {
      field.addEventListener('input', () => field.classList.remove('is-invalid'));
    });
  });

  const applyMask = (input, formatter) => {
    input.addEventListener('input', () => {
      const start = input.selectionStart;
      const before = input.value;
      input.value = formatter(before);
      if (start !== null && input === document.activeElement) {
        const delta = input.value.length - before.length;
        input.setSelectionRange(start + delta, start + delta);
      }
    });
  };

  const onlyDigits = (value, max) => value.replace(/\D+/g, '').slice(0, max);

  document.querySelectorAll('[data-mask="cnpj"]').forEach((input) => {
    applyMask(input, (value) => {
      const d = onlyDigits(value, 14);
      return d
        .replace(/^(\d{2})(\d)/, '$1.$2')
        .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1/$2')
        .replace(/(\d{4})(\d)/, '$1-$2');
    });
  });

  document.querySelectorAll('[data-mask="cpf"]').forEach((input) => {
    applyMask(input, (value) => {
      const d = onlyDigits(value, 11);
      return d
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1-$2');
    });
  });

  document.querySelectorAll('[data-mask="phone"]').forEach((input) => {
    applyMask(input, (value) => {
      const d = onlyDigits(value, 11);
      if (d.length <= 10) {
        return d
          .replace(/^(\d{2})(\d)/, '($1) $2')
          .replace(/(\d{4})(\d)/, '$1-$2');
      }
      return d
        .replace(/^(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d)/, '$1-$2');
    });
  });

  if (prefersReduced || typeof gsap === 'undefined') {
    document.querySelectorAll('[data-reveal]').forEach((el) => {
      el.style.opacity = '1';
      el.style.transform = 'none';
    });
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  // Header entrance
  gsap.from('[data-animate="header"]', {
    y: -24,
    opacity: 0,
    duration: 0.7,
    ease: 'power3.out',
  });

  // Hero stagger
  const heroItems = gsap.utils.toArray('[data-animate="hero"] > *');
  if (heroItems.length) {
    gsap.from(heroItems, {
      y: 36,
      opacity: 0,
      duration: 0.85,
      stagger: 0.12,
      ease: 'power3.out',
      delay: 0.15,
    });
  }

  // Scroll reveals
  gsap.utils.toArray('[data-reveal]').forEach((el) => {
    gsap.to(el, {
      scrollTrigger: {
        trigger: el,
        start: 'top 88%',
        toggleActions: 'play none none none',
      },
      y: 0,
      opacity: 1,
      duration: 0.75,
      ease: 'power2.out',
    });
  });

  // Soft parallax on mockup
  const mockup = document.querySelector('[data-parallax]');
  if (mockup) {
    gsap.to(mockup, {
      y: -40,
      ease: 'none',
      scrollTrigger: {
        trigger: mockup,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true,
      },
    });
  }

  // Auth card entrance
  gsap.from('.auth-card', {
    y: 24,
    opacity: 0,
    duration: 0.7,
    ease: 'power3.out',
  });

  
})();
