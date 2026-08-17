import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Settings page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function settingsEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the settings layout once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function settingsReveal(isMobile) {
  const layout = document.querySelector('.set-layout');
  if (!layout) return;

  gsap.fromTo(
    layout,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: { trigger: layout, start: 'top 92%', once: true },
      onComplete: () => gsap.set(layout, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('Settings', ({ matchMedia }) => {
  settingsEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    settingsReveal(context.conditions.mobile);
  });
});
