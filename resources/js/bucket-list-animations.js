import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Bucket list page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function bucketListEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the add-item form card once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function addFormReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Add a bucket list item"]');
  if (!section) return;

  const targets = [];

  const wrap = section.querySelector(':scope > .pl-form-wrap');
  if (wrap) targets.push(wrap);

  const empty = section.querySelector(':scope > .dash-empty');
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
      scrollTrigger: { trigger: section, start: 'top 92%', once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );
}

/**
 * Reveal the bucket list progress section once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function progressReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Bucket list progress"]');
  if (!section) return;

  const targets = [];

  const card = section.querySelector(':scope > .bl-progress-card');
  if (card) targets.push(card);

  const filters = section.querySelector(':scope > .bl-filters');
  if (filters) targets.push(filters);

  const list = section.querySelector(':scope > .bl-list');
  if (list) targets.push(...list.querySelectorAll(':scope > .bl-item'));

  const empty = section.querySelector(':scope > .dash-empty');
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

createMotionModule('BucketList', ({ matchMedia }) => {
  bucketListEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;
    addFormReveal(isMobile);
    progressReveal(isMobile);
  });
});
