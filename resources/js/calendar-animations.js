import { gsap } from 'gsap';
import { createMotionModule } from './motion-lifecycle';

/**
 * Calendar entrance – runs once on initial page load.
 * Page header fades in with a light rise.
 */
function calendarEntrance() {
  const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });

  tl.fromTo('.mem-head', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.7 });

  tl.eventCallback('onComplete', () => {
    gsap.set('.mem-head', { clearProps: 'transform,opacity' });
  });
}

/**
 * One ScrollTrigger reveal for the calendar wrap: the calendar board and
 * the day-details panel fade in, then the day cells light up in a quick
 * cascade. Transform/opacity only; clears inline styles once done.
 */
function calendarReveal(isMobile) {
  const wrap = document.querySelector('.cal-wrap[data-gsap-reveal]');
  if (!wrap) return;

  const y = isMobile ? 14 : 24;

  const cal = wrap.querySelector(':scope > .cal');
  const details = wrap.querySelector(':scope > .cal-details');
  const days = wrap.querySelectorAll('.cal-day');

  const tl = gsap.timeline({
    defaults: { ease: 'power2.out' },
    scrollTrigger: { trigger: wrap, start: 'top 88%', once: true },
  });

  if (cal) {
    tl.fromTo(cal, { opacity: 0, y }, { opacity: 1, y: 0, duration: 0.6 }, 0);
  }
  if (details) {
    tl.fromTo(details, { opacity: 0, y }, { opacity: 1, y: 0, duration: 0.6 }, 0.12);
  }
  if (days.length) {
    tl.fromTo(
      days,
      { opacity: 0, scale: 0.96 },
      { opacity: 1, scale: 1, duration: 0.45, stagger: 0.008 },
      0.35
    );
  }

  const targets = [];
  if (cal) targets.push(cal);
  if (details) targets.push(details);
  targets.push(...days);

  tl.eventCallback('onComplete', () => {
    gsap.set(targets, { clearProps: 'transform,opacity' });
  });
}

/**
 * One ScrollTrigger reveal for the events list: header and event items
 * cascade in together. Transform/opacity only; clears inline styles once done.
 */
function eventsReveal(isMobile) {
  const feed = document.querySelector('.cpl-feed-section');
  const section = feed ? feed.closest('section') : null;
  if (!section) return;

  const targets = [];

  const header = feed.querySelector(':scope > .dash-section-header');
  if (header) targets.push(header);

  const list = feed.querySelector(':scope > .ev-list');
  if (list) {
    targets.push(...list.querySelectorAll(':scope > .ev-item'));
  }

  if (!targets.length) return;

  gsap.fromTo(
    targets,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      stagger: isMobile ? 0.05 : 0.08,
      scrollTrigger: { trigger: section, start: 'top 88%', once: true },
      onComplete: () => gsap.set(targets, { clearProps: 'transform,opacity' }),
    }
  );
}

/**
 * The quiet-calendar empty state fades in as one unit.
 */
function emptyReveal(isMobile) {
  const section = document.querySelector('.dash-content > .mem-empty[data-gsap-reveal]');
  if (!section) return;

  gsap.fromTo(
    section,
    { opacity: 0, y: isMobile ? 14 : 24 },
    {
      opacity: 1,
      y: 0,
      duration: 0.7,
      ease: 'power2.out',
      scrollTrigger: { trigger: section, start: 'top 88%', once: true },
      onComplete: () => gsap.set(section, { clearProps: 'transform,opacity' }),
    }
  );
}

createMotionModule('Calendar', ({ matchMedia }) => {
  calendarEntrance();

  const mm = matchMedia();
  mm.add({ mobile: '(max-width: 680px)', desktop: '(min-width: 681px)' }, (context) => {
    const isMobile = context.conditions.mobile;
    calendarReveal(isMobile);
    eventsReveal(isMobile);
    emptyReveal(isMobile);
  });
});