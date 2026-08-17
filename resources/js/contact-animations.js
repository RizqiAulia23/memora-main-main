import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Contact page entrance – runs once on initial page load.
 * The hero eyebrow, heading and paragraph stagger in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function contactEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.contact-hero .eyebrow', { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.6 }, 0.12)
    .fromTo('.contact-hero h1', { opacity: 0, y: 28 }, { opacity: 1, y: 0, duration: 0.8 }, 0.2)
    .fromTo('.contact-hero p', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 }, 0.32);

  tl.eventCallback('onComplete', () => {
    gsap.set('.contact-hero .eyebrow, .contact-hero h1, .contact-hero p', { clearProps: 'transform,opacity' });
  });
}

/**
 * Reveal a section's elements on scroll.
 * Transform/opacity only; clears inline styles once done.
 */
function revealSection(targets, { y = 24, stagger = 0, scale = 1, duration = 0.8, start = 'top 85%' } = {}) {
  const els = gsap.utils.toArray(targets);
  if (!els.length) return;

  const from = { opacity: 0, y };
  const to = {
    opacity: 1,
    y: 0,
    duration,
    ease: 'power2.out',
    stagger,
    scrollTrigger: { trigger: els[0], start, once: true },
    onComplete: () => gsap.set(els, { clearProps: 'transform,opacity' }),
  };
  if (scale !== 1) {
    from.scale = scale;
    to.scale = 1;
  }
  gsap.fromTo(els, from, to);
}

/**
 * Section reveals – mobile uses smaller offsets and tighter stagger.
 */
function contactReveal(isMobile) {
  const y = isMobile ? 14 : 24;
  const stagger = isMobile ? 0.06 : 0.1;

  revealSection('.contact-info[data-gsap-reveal] > h2, .contact-info[data-gsap-reveal] > .info-subtitle', { y });
  revealSection('.info-card[data-gsap-reveal]', { y, stagger, start: 'top 90%' });
  revealSection('.cta-banner-inner[data-gsap-reveal]', { y: isMobile ? 20 : 28, scale: 0.97, start: 'top 88%' });
}

createMotionModule('Contact', ({ matchMedia }) => {
  contactEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    contactReveal(context.conditions.mobile);
  });
});
