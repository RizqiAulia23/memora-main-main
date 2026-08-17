import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Shared memories page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function sharedEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal one shared section: its title and cards.
 * Transform/opacity only; clears inline styles once done.
 */
function sharedSectionReveal(section, isMobile) {
  const targets = [];

  const title = section.querySelector(':scope > .shm-section-title');
  if (title) targets.push(title);

  const grid = section.querySelector(':scope > .dash-memories-grid');
  if (grid) targets.push(...grid.querySelectorAll(':scope > .dash-memory-card'));

  if (!targets.length) return;

  gsap.fromTo(
    targets,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      stagger: isMobile ? 0.05 : 0.08,
      scrollTrigger: { trigger: section, start: 'top 88%', once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );
}

/**
 * Reveal the empty state and every shared section.
 * Transform/opacity only; clears inline styles once done.
 */
function sharedReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Shared memories list"]');
  if (!section) return;

  const empty = section.querySelector(':scope > .mem-empty > .dash-empty');
  if (empty) {
    gsap.fromTo(
      empty,
      { opacity: 0, y: isMobile ? 14 : 24 },
      {
        opacity: 1,
        y: 0,
        duration: 0.7,
        ease: 'power2.out',
        scrollTrigger: { trigger: section, start: 'top 88%', once: true },
        onComplete: () => gsap.set(empty, { clearProps: 'transform,opacity' }),
      }
    );
  }

  section.querySelectorAll(':scope > .shm-section').forEach((sectionEl) => {
    sharedSectionReveal(sectionEl, isMobile);
  });
}

createMotionModule('Shared', ({ matchMedia }) => {
  sharedEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;

    const filter = document.querySelector('.shm-filter[data-gsap-reveal]');
    if (filter) {
      gsap.fromTo(
        filter,
        { opacity: 0, y: isMobile ? 14 : 24 },
        {
          opacity: 1,
          y: 0,
          duration: 0.7,
          ease: 'power2.out',
          scrollTrigger: { trigger: filter, start: 'top 92%', once: true },
          onComplete: () => gsap.set(filter, { clearProps: 'transform,opacity' }),
        }
      );
    }

    sharedReveal(isMobile);
  });
});
