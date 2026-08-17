/**
 * Memorify Animation Tokens
 *
 * Single source of truth for animation timing and easing values.
 * New animation work MUST use these values; existing modules already
 * conform (verified in the Phase 6.5 architecture audit).
 *
 * Convention:
 * - Entrance / scroll reveals    -> ENTRANCE (0.7s, power2.out)
 * - Hover states                 -> HOVER (0.25s, power2.out)
 * - Press / micro feedback       -> MICRO (0.18s, power2.out)
 * - Modal / lightbox open-close  -> MODAL (0.3s, power2.out)
 * - Stagger: desktop 0.08, mobile 0.05
 * - Vertical travel: desktop 24px, mobile 14px
 * - Mobile breakpoint: max-width 680px
 *
 * Animations must only touch transform and opacity (never layout props).
 *
 * Phase 9: the MOTION_HIERARCHY table maps semantic motion categories to
 * token values. It is the formal motion language: new animation work picks
 * a category instead of inventing ad-hoc durations. Existing tokens are
 * the source of truth and remain unchanged.
 */

export const TOKENS = {
  DURATION_ENTRANCE: 0.7,
  DURATION_REVEAL: 0.7,
  DURATION_HOVER: 0.25,
  DURATION_MICRO: 0.18,
  DURATION_MODAL: 0.3,

  EASE_STANDARD: 'power2.out',
  EASE_BACK: 'back.out(1.7)',

  STAGGER_DESKTOP: 0.08,
  STAGGER_MOBILE: 0.05,

  Y_DESKTOP: 24,
  Y_MOBILE: 14,

  MOBILE_QUERY: '(max-width: 680px)',
  DESKTOP_QUERY: '(min-width: 681px)',

  SCROLL_START_NEAR: 'top 92%',
  SCROLL_START_DEFAULT: 'top 88%',
};

/**
 * Phase 9 semantic motion hierarchy.
 *
 * page        -> largest visual movement, page entrance (one per page load).
 * section     -> moderate movement + stagger for scroll reveals.
 * component   -> shorter, localized card/item motion.
 * micro       -> very short, subtle hover/press feedback.
 * feedback    -> success/error/loading confirmation, fast and restrained.
 * modal       -> overlay / lightbox open-close.
 * navigation  -> drawer / panel movement that preserves spatial context.
 * state       -> in-place state changes (favorite, filters, etc.).
 *
 * Rules:
 * - One dominant motion event per interaction; secondary elements respond subtly.
 * - Page entrance must not compete with interactive feedback.
 * - User interaction always takes priority over decorative motion.
 * - Motion may only use transform and opacity.
 */
export const MOTION_HIERARCHY = {
  page: { duration: TOKENS.DURATION_ENTRANCE, ease: TOKENS.EASE_STANDARD, travel: TOKENS.Y_DESKTOP, stagger: 0 },
  section: { duration: TOKENS.DURATION_REVEAL, ease: TOKENS.EASE_STANDARD, travel: TOKENS.Y_DESKTOP, stagger: TOKENS.STAGGER_DESKTOP },
  component: { duration: 0.5, ease: TOKENS.EASE_STANDARD, travel: 16, stagger: 0 },
  micro: { duration: TOKENS.DURATION_MICRO, ease: TOKENS.EASE_STANDARD, travel: 0, stagger: 0 },
  feedback: { duration: TOKENS.DURATION_MODAL, ease: TOKENS.EASE_STANDARD, travel: 0, stagger: 0 },
  modal: { duration: TOKENS.DURATION_MODAL, ease: TOKENS.EASE_STANDARD, travel: 0, stagger: 0 },
  navigation: { duration: TOKENS.DURATION_MODAL, ease: TOKENS.EASE_STANDARD, travel: 0, stagger: 0 },
  state: { duration: TOKENS.DURATION_HOVER, ease: TOKENS.EASE_STANDARD, travel: 0, stagger: 0 },
};

export default TOKENS;