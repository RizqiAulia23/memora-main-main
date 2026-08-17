import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Connections page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function connectionsEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Collect the header and all remaining children of one section.
 */
function collectSectionTargets(section) {
  const targets = [];

  const header = section.querySelector(':scope > .dash-section-header');
  if (header) targets.push(header);

  Array.from(section.children).forEach((child) => {
    if (child !== header) targets.push(child);
  });

  return targets;
}

/**
 * One ScrollTrigger reveal for a section and its cards.
 * Transform/opacity only; clears inline styles once done.
 */
function revealSection(section, { y = 24, stagger = 0.08, start = 'top 88%' } = {}) {
  const targets = collectSectionTargets(section);
  if (!targets.length) return;

  gsap.fromTo(
    targets,
    { opacity: 0, y },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      stagger,
      scrollTrigger: { trigger: section, start, once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('Connections', ({ matchMedia }) => {
  connectionsEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;
    const y = isMobile ? 14 : 24;
    const stagger = isMobile ? 0.05 : 0.08;

    gsap.utils.toArray('.conn-section[data-gsap-reveal]').forEach((section) => {
      revealSection(section, { y, stagger });
    });
  });
});
