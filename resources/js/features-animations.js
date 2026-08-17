import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Features page entrance – runs once on initial page load.
 * The hero visual scales in, then the content elements stagger up.
 * Transform/opacity only; clears inline styles once done.
 */
function featuresEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.hero-visual', { opacity: 0, y: 24, scale: 0.985 }, { opacity: 1, y: 0, scale: 1, duration: 0.9 }, 0)
    .fromTo('.hero-content .hero-badge', { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.6 }, 0.12)
    .fromTo('.hero-content .hero-title', { opacity: 0, y: 28 }, { opacity: 1, y: 0, duration: 0.8 }, 0.2)
    .fromTo('.hero-content .hero-description', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 }, 0.32)
    .fromTo('.hero-content .hero-buttons .btn', { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.6, stagger: 0.08 }, 0.44)
    .fromTo('.hero-content .hero-users', { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 0.6 }, 0.56);

  tl.eventCallback('onComplete', () => {
    gsap.set(
      ['.hero-content .hero-badge', '.hero-content .hero-title', '.hero-content .hero-description', '.hero-content .hero-buttons .btn', '.hero-content .hero-users'],
      { clearProps: 'transform,opacity' }
    );
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
function featuresReveal(isMobile) {
  const y = isMobile ? 14 : 24;
  const stagger = isMobile ? 0.06 : 0.1;

  revealSection('.features .section-header[data-gsap-reveal]', { y });
  revealSection('.feature-card[data-gsap-reveal]', { y, stagger });
  revealSection('.features-cta[data-gsap-reveal]', { y: isMobile ? 10 : 16 });
  revealSection('.showcase .section-header[data-gsap-reveal]', { y });
  revealSection('.showcase-tabs[data-gsap-reveal]', { y });
  revealSection('.showcase-content[data-gsap-reveal]', { y, start: 'top 92%' });
  revealSection('.cta-card[data-gsap-reveal]', { y: isMobile ? 20 : 28, scale: 0.97, start: 'top 88%' });
}

createMotionModule('Features', ({ matchMedia }) => {
  featuresEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    featuresReveal(context.conditions.mobile);
  });
});
