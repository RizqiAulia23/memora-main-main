import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Auth page entrance – runs once on initial page load.
 * The login card fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function authCardEntrance() {
  const card = document.querySelector('.login-card');
  if (!card) return;

  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo(card, { opacity: 0, y: 24 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set(card, { clearProps: 'transform,opacity' });
  });
}

createMotionModule('Auth', () => {
  authCardEntrance();
});
