import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Favorites page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function favoritesEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Find the revealable container for the favorites grid or empty state.
 */
function findRevealTarget() {
  const grid = document.querySelector('.dash-memories-grid');
  if (grid && grid.closest('[data-gsap-reveal]')) return grid.closest('[data-gsap-reveal]');

  const empty = document.querySelector('.mem-empty .dash-empty');
  if (empty && empty.closest('[data-gsap-reveal]')) return empty.closest('[data-gsap-reveal]');

  return null;
}

/**
 * Reveal the memory cards (or empty state) once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function favoritesReveal(isMobile) {
  const section = findRevealTarget();
  if (!section) return;

  const targets = [];

  const grid = section.querySelector(':scope > .dash-memories-grid');
  if (grid) targets.push(...grid.querySelectorAll(':scope > .dash-memory-card'));

  const empty = section.querySelector(':scope > .mem-empty > .dash-empty');
  if (empty) targets.push(empty);

  const pagination = section.querySelector(':scope > .mem-pagination-wrap');
  if (pagination) targets.push(pagination);

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

createMotionModule('Favorites', ({ matchMedia }) => {
  favoritesEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    favoritesReveal(context.conditions.mobile);
  });
});
