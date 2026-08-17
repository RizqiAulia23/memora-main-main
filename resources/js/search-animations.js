import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Search page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function searchEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the search hero once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function heroReveal(isMobile) {
  const hero = document.querySelector('.search-hero[data-gsap-reveal]');
  if (!hero) return;

  gsap.fromTo(
    hero,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: { trigger: hero, start: 'top 92%', once: true },
      onComplete: () => gsap.set(hero, { clearProps: 'transform,opacity' }),
    }
  );
}

/**
 * Reveal the search results once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function resultsReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Search results"]');
  if (!section) return;

  const targets = [];

  const emptyHint = section.querySelector(':scope > .search-results > .search-empty-hint');
  if (emptyHint) targets.push(emptyHint);

  const results = section.querySelector(':scope > .search-results');
  if (results) {
    targets.push(...results.querySelectorAll(':scope > .search-group-title'));
    targets.push(...results.querySelectorAll(':scope > .search-card-list > .search-card'));
    targets.push(...results.querySelectorAll(':scope > .search-photo-grid > .search-photo'));
  }

  const empty = section.querySelector(':scope > .mem-empty > .dash-empty');
  if (empty) targets.push(empty);

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

createMotionModule('Search', ({ matchMedia }) => {
  searchEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;
    heroReveal(isMobile);
    resultsReveal(isMobile);
  });
});
