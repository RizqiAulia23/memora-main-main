import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Profile page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function profileEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the profile cards once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function profileReveal(isMobile) {
  const layout = document.querySelector('.prof-layout[data-gsap-reveal]');
  if (!layout) return;

  const cards = layout.querySelectorAll(':scope > .prof-card, :scope > .prof-main > .prof-card');
  if (!cards.length) return;

  gsap.fromTo(
    cards,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      stagger: isMobile ? 0.05 : 0.08,
      scrollTrigger: { trigger: layout, start: 'top 92%', once: true },
      onComplete: () => gsap.set(cards, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('Profile', ({ matchMedia }) => {
  profileEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    profileReveal(context.conditions.mobile);
  });
});
