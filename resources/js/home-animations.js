import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { createMotionModule } from './motion-lifecycle';

/**
 * Hero entrance – runs once on initial page load.
 * Copy fades in with a light stagger, the visual scales in gently.
 */
function heroEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo(
    '.hero-visual',
    { opacity: 0, y: 24, scale: 0.985 },
    { opacity: 1, y: 0, scale: 1, duration: 0.9 },
    0
  )
    .fromTo(
      '.hero-copy .eyebrow',
      { opacity: 0, y: 16 },
      { opacity: 1, y: 0, duration: 0.6 },
      0.12
    )
    .fromTo(
      '.hero-copy h1',
      { opacity: 0, y: 28 },
      { opacity: 1, y: 0, duration: 0.8 },
      0.2
    )
    .fromTo(
      '.hero-copy .lead',
      { opacity: 0, y: 20 },
      { opacity: 1, y: 0, duration: 0.7 },
      0.32
    )
    .fromTo(
      '.hero-ctas .btn',
      { opacity: 0, y: 16 },
      { opacity: 1, y: 0, duration: 0.6, stagger: 0.08 },
      0.44
    )
    .fromTo(
      '.avatars',
      { opacity: 0, y: 14 },
      { opacity: 1, y: 0, duration: 0.6 },
      0.56
    );

  tl.eventCallback('onComplete', () => {
    gsap.set(
      ['.hero-copy .eyebrow', '.hero-copy h1', '.hero-copy .lead', '.hero-ctas .btn', '.avatars'],
      { clearProps: 'transform,opacity' }
    );
  });
}

/**
 * Subtle scroll parallax on the hero visual (desktop only).
 * Transform-only, so the layout never shifts.
 */
function heroParallax() {
  gsap.to('.hero-visual', {
    yPercent: 5,
    ease: 'none',
    scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: 0.6 },
  });
}

/**
 * Very small idle float on the hero visual (desktop only).
 * Starts after the entrance completes; transform-only.
 */
function heroFloat() {
  gsap.to('.hero-visual', {
    y: -4,
    delay: 1.2,
    duration: 5,
    ease: 'sine.inOut',
    yoyo: true,
    repeat: -1,
  });
}

/**
 * Image reveals: fade + slight scale, one ScrollTrigger per image group
 * with a light stagger instead of one trigger per image.
 */
function imageReveals() {
  const groups = [
    ['.gallery-mosaic img', 0.12],
    ['.pb-strip img', 0.08],
    ['.cta-calendar img', 0],
  ];

  groups.forEach(([selector, stagger]) => {
    const imgs = gsap.utils.toArray(selector);
    if (!imgs.length) return;

    gsap.fromTo(
      imgs,
      { opacity: 0, scale: 1.03 },
      {
        opacity: 1,
        scale: 1,
        duration: 0.9,
        ease: 'power2.out',
        stagger,
        scrollTrigger: { trigger: imgs[0].parentElement, start: 'top 88%', once: true },
        onComplete: () => gsap.set(imgs, { clearProps: 'transform,opacity' }),
      }
    );
  });
}

/**
 * One ScrollTrigger reveal for a group of elements.
 * Transform/opacity only, clears inline styles once done.
 */
function revealOnScroll(selector, { y = 24, stagger = 0, scale = 1, duration = 0.8, start = 'top 85%' } = {}) {
  const targets = gsap.utils.toArray(selector);
  if (!targets.length) return;

  const from = { opacity: 0, y };
  const to = {
    opacity: 1,
    y: 0,
    duration,
    ease: 'power2.out',
    stagger,
    scrollTrigger: { trigger: targets[0], start, once: true },
    onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
  };
  if (scale !== 1) {
    from.scale = scale;
    to.scale = 1;
  }

  gsap.fromTo(targets, from, to);
}

/**
 * Scroll reveals for homepage sections. Movement and stagger are reduced
 * on small viewports so mobile stays calm.
 */
function sectionReveals(isMobile) {
  const y = isMobile ? 14 : 24;
  const stagger = isMobile ? 0.06 : 0.1;

  revealOnScroll('#features .container > .reveal:not(.see-all-wrap)', { y });
  revealOnScroll('.feature-grid .feature-card', { y, stagger });
  revealOnScroll('.see-all-wrap.reveal', { y: isMobile ? 10 : 16 });
  revealOnScroll('.showcase-head.reveal', { y });
  revealOnScroll('.tab-grid .tab-card', { y, stagger });
  revealOnScroll('.cta-banner.reveal', { y: isMobile ? 20 : 28, scale: 0.97, start: 'top 88%' });
}

createMotionModule('Home', ({ matchMedia }) => {
  heroEntrance();
  imageReveals();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    sectionReveals(context.conditions.mobile);
  });
  mm.add('(min-width: 1101px)', () => {
    heroParallax();
    heroFloat();
  });
});