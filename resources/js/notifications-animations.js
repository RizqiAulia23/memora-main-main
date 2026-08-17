import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Notifications page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function notificationsEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the notification items once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function notifReveal(isMobile) {
  const section = document.querySelector('section[aria-label="Notifications list"]');
  if (!section) return;

  const targets = [];

  const list = section.querySelector(':scope > .notif-page-list');
  if (list) targets.push(...list.querySelectorAll(':scope > .notif-page-item'));

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

createMotionModule('Notifications', ({ matchMedia }) => {
  notificationsEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    notifReveal(context.conditions.mobile);
  });
});
