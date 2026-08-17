import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Letter detail page entrance – runs once on initial page load.
 * The letter heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function letterShowEntrance() {
  const head = document.querySelector('.letter-head');
  if (!head) return;

  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo(head, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set(head, { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the letter paper once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function paperReveal(isMobile) {
  const paper = document.querySelector('.letter-paper[data-gsap-reveal]');
  if (!paper) return;

  gsap.fromTo(
    paper,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.8,
      ease: 'power2.out',
      scrollTrigger: { trigger: paper, start: 'top 88%', once: true },
      onComplete: () => gsap.set(paper, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('LettersShow', ({ matchMedia }) => {
  letterShowEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    paperReveal(context.conditions.mobile);
  });
});
