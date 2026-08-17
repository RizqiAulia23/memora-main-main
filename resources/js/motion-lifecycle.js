import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Memorify Motion Lifecycle Utility (Phase 9)
 *
 * Single reusable lifecycle for every page animation module. Replaces the
 * previously duplicated per-module boilerplate (init-once guard, reduced
 * motion gate, gsap.context, matchMedia wiring, revert-on-failure, destroy,
 * isActive, window registration, auto-init).
 *
 * Behavior is identical to the Phase 1-8 modules it consolidates:
 * - init() is idempotent (duplicate-init protected).
 * - Reduced motion short-circuits before any tween or ScrollTrigger is
 *   created; content stays visible via CSS (`.reveal[data-gsap-reveal]`).
 * - On failure everything is reverted so the page stays fully usable.
 * - destroy() reverts the context and any matchMedia instance, killing all
 *   tweens, ScrollTriggers and listeners created by the module.
 * - The module registers window.<Name>Animations = { init<Name>Animations,
 *   destroy<Name>Animations, isActive } and auto-inits on DOMContentLoaded,
 *   exactly as before.
 */

/**
 * Whether the user prefers reduced motion.
 */
export function prefersReducedMotion() {
  return typeof window !== 'undefined'
    && typeof window.matchMedia === 'function'
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Create a page animation module.
 *
 * @param {string} name  Module name used for the window API, e.g. 'Home'
 *                       registers window.HomeAnimations.
 * @param {Function} setup  Runs inside the module context, exactly once per
 *                       init. Receives { gsap, ScrollTrigger, matchMedia }.
 *                       matchMedia() returns a fresh gsap.matchMedia() whose
 *                       lifetime is bound to the module (reverted on destroy).
 * @returns {{init: Function, destroy: Function, isActive: Function}}
 */
export function createMotionModule(name, setup) {
  let ctx = null;
  let mm = null;
  let initialized = false;

  function init() {
    if (initialized) return;
    initialized = true;

    if (prefersReducedMotion()) return;

    try {
      ctx = gsap.context(() => {
        setup({
          gsap,
          ScrollTrigger,
          matchMedia: () => (mm = gsap.matchMedia()),
        });
      }, document.body);

      if (window.MemorifyAnimations && typeof window.MemorifyAnimations.refreshScrollTriggers === 'function') {
        window.MemorifyAnimations.refreshScrollTriggers();
      }
    } catch (error) {
      destroy();
    }
  }

  function destroy() {
    if (ctx) {
      ctx.revert();
      ctx = null;
    }
    if (mm) {
      mm.revert();
      mm = null;
    }
    initialized = false;
  }

  function isActive() {
    return initialized && ctx !== null;
  }

  const api = { init, destroy, isActive };

  if (typeof window !== 'undefined') {
    window[`${name}Animations`] = {
      [`init${name}Animations`]: api.init,
      [`destroy${name}Animations`]: api.destroy,
      isActive: api.isActive,
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
    } else {
      init();
    }
  }

  return api;
}
