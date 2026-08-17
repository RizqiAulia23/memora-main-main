import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Gallery page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function galleryEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the gallery grid items once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function galleryReveal(isMobile) {
  const section = document.querySelector('[data-gallery][data-gsap-reveal]');
  if (!section) return;

  const targets = [];

  const grid = section.querySelector('[data-gallery-grid]');
  if (grid) targets.push(...grid.querySelectorAll(':scope > .gal-item'));

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
      stagger: isMobile ? 0.04 : 0.06,
      scrollTrigger: { trigger: section, start: 'top 88%', once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('Gallery', ({ matchMedia }) => {
  galleryEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    galleryReveal(context.conditions.mobile);
  });
});
