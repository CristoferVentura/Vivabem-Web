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

  if (prefersReduced || typeof gsap === 'undefined') {
    document.querySelectorAll('[data-reveal], [data-pillar-reveal]').forEach((el) => {
      el.style.opacity = '1';
      el.style.transform = 'none';
      el.style.visibility = 'visible';
    });
    document.querySelectorAll('[data-pillars-section]').forEach((section) => {
      section.classList.add('is-complete');
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

  // Pilares — scroll trava e revela/oculta um pilar por vez (ida e volta)
  const pillarsSection = document.querySelector('[data-pillars-section]');
  if (pillarsSection) {
    const pillarsCheckpoint = pillarsSection.querySelector('[data-pillars-checkpoint]');
    const pillarsPin = pillarsSection.querySelector('[data-pillars-pin]');
    const pillars = pillarsSection.querySelectorAll('[data-pillar-reveal]');

    const setPinning = (active) => {
      pillarsPin.classList.toggle('is-pinned', active);
      pillarsSection.classList.toggle('is-pinning', active);
    };

    const hidePillars = () => {
      pillars.forEach((pillar) => {
        gsap.to(pillar, {
          opacity: 0,
          y: 20,
          visibility: 'hidden',
          duration: 0.8,
          ease: 'power1.out',
          overwrite: 'auto',
        });
      });
    };

    const showPillarsUpTo = (lastIndex) => {
      pillars.forEach((pillar, index) => {
        const visible = index <= lastIndex;
        gsap.to(pillar, {
          opacity: visible ? 1 : 0,
          y: visible ? 0 : 20,
          visibility: visible ? 'visible' : 'hidden',
          duration: 0.8,
          ease: 'power1.out',
          overwrite: 'auto',
        });
      });
    };

    const getVisiblePillarCount = (progress) => {
      const segments = pillars.length + 1;
      const activeSegment = Math.floor(progress * segments);
      return Math.max(0, Math.min(pillars.length, activeSegment));
    };

    const updatePillarsByProgress = (progress) => {
      const visibleCount = getVisiblePillarCount(progress);
      if (visibleCount <= 0) {
        hidePillars();
        return;
      }
      showPillarsUpTo(visibleCount - 1);
    };

    if (pillars.length && pillarsPin && pillarsCheckpoint) {
      scrollPageToTop();

      const setupPinnedScroll = () => {
        hidePillars();

        ScrollTrigger.create({
          id: 'pillars-scroll',
          trigger: pillarsCheckpoint,
          start: () => `top top+=${getHeaderHeight()}`,
          end: () => `+=${window.innerHeight * (pillars.length + 1)}`,
          pin: pillarsPin,
          pinSpacing: true,
          anticipatePin: 0,
          invalidateOnRefresh: true,
          onEnter: () => setPinning(true),
          onEnterBack: () => {
            pillarsSection.classList.remove('is-complete');
            setPinning(true);
            const trigger = ScrollTrigger.getById('pillars-scroll');
            if (trigger) updatePillarsByProgress(trigger.progress);
          },
          onLeave: () => {
            setPinning(false);
            pillarsSection.classList.add('is-complete');
            showPillarsUpTo(pillars.length - 1);
          },
          onLeaveBack: () => {
            setPinning(false);
            pillarsSection.classList.remove('is-complete');
            hidePillars();
          },
          onUpdate: (self) => updatePillarsByProgress(self.progress),
        });
      };

      const setupSimpleReveal = () => {
        hidePillars();

        ScrollTrigger.create({
          trigger: pillarsSection,
          start: 'top 82%',
          once: true,
          onEnter: () => {
            gsap.to(pillars, {
              opacity: 1,
              y: 0,
              visibility: 'visible',
              duration: 0.8,
              stagger: 0.3,
              ease: 'power1.out',
              onComplete: () => pillarsSection.classList.add('is-complete'),
            });
          },
        });
      };

      ScrollTrigger.matchMedia({
        '(min-width: 901px)': setupPinnedScroll,
        '(max-width: 900px)': setupSimpleReveal,
      });

      scrollPageToTop();
      ScrollTrigger.refresh();
    }
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
      duration: 1,
      ease: 'power2.inOut',
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
