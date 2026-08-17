import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Letters page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function lettersEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal one letter section: its title and cards.
 * Transform/opacity only; clears inline styles once done.
 */
function lettersSectionReveal(section, isMobile) {
  const targets = [];

  const title = section.querySelector(':scope > .letter-section-title');
  if (title) targets.push(title);

  const list = section.querySelector(':scope > .letter-list');
  if (list) targets.push(...list.querySelectorAll(':scope > .letter-card'));

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

/**
 * Reveal the empty state and every letter section.
 * Transform/opacity only; clears inline styles once done.
 */
function lettersReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Love letters list"]');
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

  section.querySelectorAll(':scope > .letter-section').forEach((sectionEl) => {
    lettersSectionReveal(sectionEl, isMobile);
  });
}

createMotionModule('Letters', ({ matchMedia }) => {
  lettersEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    lettersReveal(context.conditions.mobile);
  });
});
