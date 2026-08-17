import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Memory detail entrance – runs once on initial page load.
 * The photo scales in gently, the body copy follows with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function memoryDetailEntrance() {
  const media = document.querySelector('.mem-detail-media');
  const body = document.querySelector('.mem-detail-body');
  if (!media && !body) return;

  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  if (media) {
    tl.fromTo(
      media,
      { opacity: 0, y: 20, scale: 1.02 },
      { opacity: 1, y: 0, scale: 1, duration: 0.8 },
      0
    );
  }
  if (body) {
    tl.fromTo(
      body,
      { opacity: 0, y: 24 },
      { opacity: 1, y: 0, duration: 0.7 },
      0.15
    );
  }

  tl.eventCallback('onComplete', () => {
    gsap.set([media, body], { clearProps: 'transform,opacity' });
  });
}

createMotionModule('MemoriesShow', () => {
  memoryDetailEntrance();
});