import gsap from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Dashboard entrance – runs once on initial page load.
 * Welcome, statistics, then stat cards stagger in quickly.
 */
function dashboardEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.dash-welcome', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 }, 0)
    .fromTo('.dash-stats', { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.6 }, 0.15)
    .fromTo(
      '.dash-stat-card',
      { opacity: 0, y: 16 },
      { opacity: 1, y: 0, duration: 0.55, stagger: 0.08 },
      0.3
    );

  tl.eventCallback('onComplete', () => {
    gsap.set(['.dash-welcome', '.dash-stats', '.dash-stat-card'], { clearProps: 'transform,opacity' });
  });
}

/**
 * Collect the leaf targets worth animating inside one section:
 * the header plus the card children of its body grids.
 */
function collectSectionTargets(section) {
  const targets = [];

  const header = section.querySelector(':scope > .dash-section-header');
  if (header) targets.push(header);

  const body = section.querySelector(':scope > .dash-section-body, :scope > .cpl-overview-grid');
  if (body) {
    const children = Array.from(body.children);
    if (!children.length) {
      targets.push(body);
    } else {
      children.forEach((child) => {
        const cards = child.querySelectorAll(
          ':scope > .dash-memory-card, :scope > .dash-timeline-item, :scope > .dash-gallery-thumb, ' +
          ':scope > .dash-letter-card, :scope > .dash-action-btn, :scope > .dash-activity-item, :scope > .cpl-ov-card'
        );
        if (cards.length) {
          targets.push(...cards);
        } else {
          targets.push(child);
        }
      });
    }
  }

  const extras = section.querySelectorAll(':scope > .dash-anniversary');
  if (extras.length) targets.push(...extras);

  return targets;
}

/**
 * One ScrollTrigger reveal for a section and its cards.
 * Transform/opacity only; clears inline styles once done.
 */
function revealSection(section, { y = 24, stagger = 0.08, start = 'top 88%' } = {}) {
  const targets = collectSectionTargets(section);
  if (!targets.length) return;

  gsap.fromTo(
    targets,
    { opacity: 0, y },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      stagger,
      scrollTrigger: { trigger: section, start, once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('Dashboard', ({ matchMedia }) => {
  dashboardEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;
    const y = isMobile ? 14 : 24;
    const stagger = isMobile ? 0.05 : 0.08;

    const overview = document.querySelector('.cpl-overview');
    if (overview) revealSection(overview, { y, stagger });

    gsap.utils.toArray('.dash-section[data-gsap-reveal]').forEach((section) => {
      revealSection(section, { y, stagger });
    });
  });
});
