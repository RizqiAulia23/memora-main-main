import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Letters form page entrance – runs once on initial page load.
 * The page heading fades in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function lettersFormEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal the form card once on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function formReveal(isMobile) {
  const wrap = document.querySelector('.letter-form-wrap.reveal');
  if (!wrap) return;

  gsap.fromTo(
    wrap,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: { trigger: wrap, start: 'top 92%', once: true },
      onComplete: () => gsap.set(wrap, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('LettersForm', ({ matchMedia }) => {
  lettersFormEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    formReveal(context.conditions.mobile);
  });
});
