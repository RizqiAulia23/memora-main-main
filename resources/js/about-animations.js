import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * About page entrance – runs once on initial page load.
 * The hero eyebrow, heading and paragraph stagger in with a light rise.
 * Transform/opacity only; clears inline styles once done.
 */
function aboutEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.about-hero .eyebrow', { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.6 }, 0.12)
    .fromTo('.about-hero h1', { opacity: 0, y: 28 }, { opacity: 1, y: 0, duration: 0.8 }, 0.2)
    .fromTo('.about-hero p', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 }, 0.32);

  tl.eventCallback('onComplete', () => {
    gsap.set('.about-hero .eyebrow, .about-hero h1, .about-hero p', { clearProps: 'transform,opacity' });
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
function aboutReveal(isMobile) {
  const y = isMobile ? 14 : 24;
  const stagger = isMobile ? 0.06 : 0.1;

  revealSection('.story-images[data-gsap-reveal], .story-copy[data-gsap-reveal]', { y });
  revealSection('.stat-card[data-gsap-reveal]', { y, stagger, start: 'top 90%' });
  revealSection('.values .section-eyebrow[data-gsap-reveal], .values h2[data-gsap-reveal]', { y });
  revealSection('.value-card[data-gsap-reveal]', { y, stagger });
  revealSection('.team .section-eyebrow[data-gsap-reveal], .team h2[data-gsap-reveal]', { y });
  revealSection('.team-card[data-gsap-reveal]', { y, stagger });
  revealSection('.about-cta-banner[data-gsap-reveal]', { y: isMobile ? 20 : 28, scale: 0.97, start: 'top 88%' });
}

createMotionModule('About', ({ matchMedia }) => {
  aboutEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    aboutReveal(context.conditions.mobile);
  });
});
