import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Lenis from 'lenis';
import 'lenis/dist/lenis.css';
import { TOKENS } from './animation-tokens';

gsap.registerPlugin(ScrollTrigger);

/**
 * Memorify Animation Core
 *
 * Foundation layer for the Memorify animation system:
 * GSAP + ScrollTrigger + Lenis smooth scrolling, wired to the GSAP
 * ticker and driven exactly once per page load.
 *
 * Phase 1 intentionally creates NO page animations. It only provides
 * the safe, reusable core that later phases build upon.
 */

const LENIS_OPTIONS = {
  lerp: 0.09,
  wheelMultiplier: 1,
  touchMultiplier: 1,
  smoothWheel: true,
  syncTouch: false,
  autoResize: true,
  anchors: true,
  autoToggle: true,
  respectReducedMotion: true,
};

let lenis = null;
let initialized = false;

/**
 * Whether the user prefers reduced motion.
 */
export function prefersReducedMotion() {
  return typeof window.matchMedia === 'function'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * The active Lenis instance, or null when smooth scrolling is disabled.
 */
export function getLenis() {
  return lenis;
}

/**
 * Ask ScrollTrigger to recompute all trigger positions.
 * Safe to call at any time; no-op before initialization.
 */
export function refreshScrollTriggers() {
  if (initialized) {
    ScrollTrigger.refresh();
  }
}

/**
 * Initialize the animation core exactly once.
 *
 * - Registers ScrollTrigger with GSAP.
 * - Creates a single Lenis instance with a subtle premium feel.
 * - Drives Lenis from GSAP's ticker (one animation loop).
 * - Keeps ScrollTrigger in sync with Lenis.
 * - Falls back to native scrolling when reduced motion is preferred
 *   or when initialization fails.
 */
export function initMemorifyAnimations() {
  if (initialized) {
    return;
  }
  initialized = true;

  if (prefersReducedMotion()) {
    return;
  }

  try {
    lenis = new Lenis(LENIS_OPTIONS);

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => {
      lenis.raf(time * 1000);
    });
    gsap.ticker.lagSmoothing(0);

    window.MemorifyAnimations.lenis = lenis;
  } catch (error) {
    if (lenis) {
      lenis.destroy();
      lenis = null;
    }
  }
}

/**
 * Tear the animation core down (mainly useful for future phases and tests).
 */
export function destroyMemorifyAnimations() {
  if (lenis) {
    lenis.destroy();
    lenis = null;
  }
  initialized = false;

  if (typeof window !== 'undefined' && window.MemorifyAnimations) {
    window.MemorifyAnimations.lenis = null;
  }
}

/**
 * Debug snapshot for QA tooling: Lenis state and live ScrollTrigger count.
 */
export function debugState() {
  return {
    lenis: lenis !== null,
    scrollTriggers: initialized ? ScrollTrigger.getAll().length : 0,
  };
}

if (typeof window !== 'undefined') {
  window.MemorifyAnimations = {
    lenis: null,
    tokens: TOKENS,
    gsap,
    initMemorifyAnimations,
    destroyMemorifyAnimations,
    prefersReducedMotion,
    refreshScrollTriggers,
    debugState,
  };
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initMemorifyAnimations);
} else {
  initMemorifyAnimations();
}