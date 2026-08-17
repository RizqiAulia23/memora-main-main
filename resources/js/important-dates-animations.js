import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Important dates page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function importantDatesEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the add-date form card once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function addFormReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Add an important date"]');
  if (!section) return;

  const wrap = section.querySelector(':scope > .id-form-wrap');
  if (!wrap) return;

  gsap.fromTo(
    wrap,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: { trigger: section, start: 'top 92%', once: true },
      onComplete: () => gsap.set(wrap, { clearProps: 'transform,opacity' }),
    }
  );
}

/**
 * Reveal the dates list once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function datesReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Your important dates"]');
  if (!section) return;

  const targets = [];

  const header = section.querySelector(':scope > .cpl-feed-section > .dash-section-header');
  if (header) targets.push(header);

  const grid = section.querySelector(':scope > .cpl-feed-section > .id-grid');
  if (grid) targets.push(...grid.querySelectorAll(':scope > .id-card'));

  const empty = section.querySelector(':scope > .cpl-feed-section > .dash-empty');
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

createMotionModule('ImportantDates', ({ matchMedia }) => {
  importantDatesEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;
    addFormReveal(isMobile);
    datesReveal(isMobile);
  });
});
