import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Couple timeline page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function coupleTimelineEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the feed items once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function feedReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Couple timeline feed"]');
  if (!section) return;

  const targets = [];

  const feed = section.querySelector(':scope > .cpl-feed');
  if (feed) targets.push(...feed.querySelectorAll(':scope > .cpl-feed-item'));

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

createMotionModule('CoupleTimeline', ({ matchMedia }) => {
  coupleTimelineEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    feedReveal(context.conditions.mobile);
  });
});
