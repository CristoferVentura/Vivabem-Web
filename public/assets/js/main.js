(() => {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }

  const scrollPageToTop = () => {
    if (!window.location.hash) {
      window.scrollTo({ top: 0, left: 0, behavior: 'instant' in window ? 'instant' : 'auto' });
    }
  };

  scrollPageToTop();

  window.addEventListener('DOMContentLoaded', scrollPageToTop);
  window.addEventListener('load', scrollPageToTop);

  const getHeaderHeight = () => {
    const value = getComputedStyle(document.documentElement).getPropertyValue('--header-h').trim();
    const parsed = parseInt(value, 10);
    return Number.isFinite(parsed) ? parsed : 84;
  };

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

  // Setores da equipe — accordion abrir/fechar
  document.querySelectorAll('[data-team-sector]').forEach((sector) => {
    const header = sector.querySelector('.team-sector__header');
    const panel = sector.querySelector('.team-sector__panel');
    if (!header || !panel) return;

    const setOpen = (open) => {
      sector.classList.toggle('is-open', open);
      header.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    header.addEventListener('click', () => {
      setOpen(!sector.classList.contains('is-open'));
    });
  });

  // Native reveals work offline and honor reduced-motion preferences.
  document.querySelectorAll('[data-pillars-section]').forEach(section => section.classList.add('is-complete'));
  if (!prefersReduced && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.remove('reveal-pending');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.05 });
    document.querySelectorAll('[data-reveal]').forEach(element => {
      element.classList.add('reveal-pending');
      observer.observe(element);
    });
  }
})();
