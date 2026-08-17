import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Timeline page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function timelineEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the timeline years once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function yearsReveal(isMobile) {
  const years = document.querySelector('.timeline-years[data-gsap-reveal]');
  if (!years) return;

  const yearEls = years.querySelectorAll(':scope > .timeline-year');
  const targets = yearEls.length ? Array.from(yearEls) : [years];

  gsap.fromTo(
    targets,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      stagger: isMobile ? 0.05 : 0.08,
      scrollTrigger: { trigger: years, start: 'top 92%', once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );
}

/**
 * Reveal one month: its label, items and item dots.
 * Transform/opacity only; clears inline styles once done.
 */
function monthReveal(month, isMobile) {
  const y = isMobile ? 14 : 24;
  const stagger = isMobile ? 0.05 : 0.08;

  const label = month.querySelector(':scope > .timeline-month-label');
  const items = month.querySelectorAll(':scope > .timeline-month-list > .timeline-item');
  const targets = [];

  if (label) targets.push(label);
  targets.push(...items);

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
      scrollTrigger: { trigger: month, start: 'top 88%', once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );

  const dots = month.querySelectorAll(':scope > .timeline-month-list > .timeline-item > .timeline-item-dot');
  if (!dots.length) return;

  gsap.fromTo(
    dots,
    { opacity: 0, scale: 0.5 },
    {
      opacity: 1,
      scale: 1,
      duration: 0.45,
      ease: 'back.out(1.7)',
      stagger,
      scrollTrigger: { trigger: month, start: 'top 88%', once: true },
      onComplete: () => gsap.set(dots, { clearProps: 'transform,opacity' }),
    }
  );
}

/**
 * Reveal the empty state once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function emptyReveal(isMobile) {
  const empty = document.querySelector('.dash-content > .mem-empty[data-gsap-reveal]');
  if (!empty) return;

  gsap.fromTo(
    empty,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: { trigger: empty, start: 'top 88%', once: true },
      onComplete: () => gsap.set(empty, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('Timeline', ({ matchMedia }) => {
  timelineEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;

    yearsReveal(isMobile);
    emptyReveal(isMobile);

    const timeline = document.querySelector('.timeline[data-gsap-reveal]');
    if (timeline) {
      timeline.querySelectorAll(':scope > .timeline-month').forEach((month) => {
        monthReveal(month, isMobile);
      });
    }
  });
});
