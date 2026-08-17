# Animation Architecture Audit — Phase 6.5

**Date:** 2026-08-17
**Scope:** All GSAP/Lenis animation code across Phases 1–6 (core, home, dashboard, and every module page, form page, auth, and settings).
**Verdict:** **PASS** — architecture is sound, consistent, and performant. Two real defects found and fixed; one flaky pre-existing test observed (not caused by animation work); no refactor required before Phase 7.

---

## 1. Inventory

| Area | Count | Notes |
|---|---|---|
| Animation modules (`resources/js/*-animations.js`) | **32** | 1 core (`memorify-animations.js`) + 31 page/form modules |
| Views with a Vite animation module | **35** | Every view with `data-gsap-reveal` has a module; `gallery/_grid.blade.php` is a partial included by `gallery/index` (same module) |
| Views with `data-gsap-reveal` markup | 36 (35 views + 1 partial) | 100 % coverage — no orphan markup, no orphan module |
| Vite inputs | 33 | `app.css`, `app.js`, 31 modules — all registered, build succeeds (~0.9 s) |
| CSS files with GSAP neutralization block `.reveal[data-gsap-reveal]` | 19 | All CSS files that style `.reveal` elements |
| Legacy `.reveal` elements in views | 135 | **All 135 also carry `data-gsap-reveal`** — legacy `main.js` IntersectionObserver is fully superseded (see §8, debt) |
| Animation dependencies | 2 | `gsap ^3.15.0`, `lenis ^1.3.26`. No Anime.js, no other engine |
| ScrollTriggers per module | 0–4 | `timeline` 4, `calendar`/`home`/`shared` 3, most 1–2, `auth`/`memories-show` 0 (entrance-only) |
| GSAP tweens per module | 0–4 | matches ScrollTrigger count + entrance |

## 2. Consistency Audit — PASS

All 31 non-core modules share identical structure (verified by read + grep across all files):

- `import gsap from 'gsap'` + `CSSPlugin` + `ScrollTrigger`, `gsap.registerPlugin(CSSPlugin, ScrollTrigger)`
- `let ctx = null; let mm = null; let initialized = false;`
- `prefersReducedMotion()` — delegates to `window.MemorifyAnimations` with `matchMedia` fallback (30/31 modules; core defines the canonical function)
- Entrance `fromTo('.mem-head', {opacity:0, y:20} → {opacity:1, y:0, duration:0.7})` + `clearProps` on complete
- ScrollTrigger reveals `start: 'top 88%'|'top 92%'`, `once: true`, `clearProps` on complete
- Mobile/desktop via `gsap.matchMedia()` `(max-width: 680px)` / `(min-width: 681px)`, mobile `y: 14` vs desktop `y: 24`, stagger `0.05` vs `0.08`
- `window.<X>Animations = { init, destroy, isActive }` namespace, `DOMContentLoaded` auto-init (with readyState guard)
- `ctx.revert()` / `mm.revert()` in `destroy()` + failure catch; `isActive()` returns `initialized && ctx !== null`

**Intentional deviations (by design, not defects):**
- `memorify-animations.js` (core): `import { gsap }`, no CSSPlugin import, Lenis integration, no matchMedia — the core is the shared base for everything else.
- `auth-animations.js`, `memories-show-animations.js`: no `matchMedia` — entrance-only pages, single viewport.

**Fixed during audit (consistency):**
- `memories-show-animations.js` declared dead `let mm = null` and `mm.revert()` blocks with no matchMedia usage — inconsistent with `auth-animations.js` (its only peer). Removed (5 lines). Zero behavior change.

## 3. Duplication Audit — QUANTIFIED, NO REFACTOR

The 31-page-module boilerplate is deliberately repeated. Quantification:

- Boilerplate block (imports → `prefersReducedMotion` → init/destroy/isActive → window export → auto-init): **identical in 28–31 of 31 modules** per component (see table below)
- Entrance pattern `.mem-head` y:20→0: **~28 modules** (exceptions: contact/features/about hero variants, memories-show, auth card)
- `clearProps` cleanup: 30/31, `once: true`: 28/31, `matchMedia` 680px breakpoint: 28/31

| Pattern | Modules |
|---|---|
| `import gsap from 'gsap'` | 30/31 (core uses named import) |
| `registerPlugin(CSSPlugin, ScrollTrigger)` | 31/31 |
| `prefersReducedMotion` | 31/31 |
| `let ctx = null` | 30/31 |
| `matchMedia` 680px | 28/31 (auth + memories-show by design) |
| `once: true` | 28/31 |
| `clearProps` | 30/31 |
| `window.<X>Animations` + DOMContentLoaded auto-init | 31/31 |
| `destroy()` + `ctx.revert()` | 31/31 |

**Decision: no shared base-class extraction.** A shared factory (e.g. `createPageAnimations({...})`) would consolidate ~70 boilerplate lines per module (~2,000 lines total) but carries real risk: every module has slightly different selectors/triggers, and GSAP context scoping makes abstraction error-prone. The duplication is mechanical, tested, and uniform — consolidation is a "nice-to-have" for Phase 7+, not a defect. This matches the audit rule: refactor only when it removes real duplication *and* improves maintainability without behavior change. Note for future: if Phase 7 adds more modules, extract a shared `pageAnimationBase.js` helper *before* adding new modules, and keep existing ones untouched.

## 4. Performance Audit — PASS

- **Composite-only properties:** all tweens animate `opacity`, `transform` (y/scale) — verified zero layout-property animations (no width/height/top/left/margin/padding).
- **Transform/opacity cleared** via `clearProps` on complete in every module — no inline-style residue, no forced layout after animation.
- **ScrollTrigger count is minimal:** 0–4 per page, all `once: true` (no ongoing scroll work after reveal).
- **Zero per-frame DOM queries:** all `querySelector*` calls are init-time only (0–9 per module).
- **GSAP + Lenis integration in core** with `refreshScrollTriggers()` called after every module init — no scroll jank from trigger miscalculation.
- **Entrance timelines** run once and are reverted/cleared; no `repeat`/`yoyo` loops anywhere except intentional ambient elements in core.
- **Reduced-motion short-circuits before any GSAP work** — zero wasted work under `prefers-reduced-motion`.
- Build size: per-module chunks 1.9–6 kB gzipped, core 5.7 kB, ScrollTrigger 44 kB (shared chunk).

## 5. Dependency Audit — PASS

- `gsap ^3.15.0` — current stable; single engine across the app; GSAP 3 API used correctly (no v2 leftovers).
- `lenis ^1.3.26` — only used in core for smooth scrolling; integrated with ScrollTrigger.
- **No Anime.js** (per Phase 6 instruction, confirmed — it is NOT installed).
- No unused animation dependencies; no duplicate engines; no runtime CDN loads for GSAP (bundled via Vite).
- All modules registered with `CSSPlugin` — required for `clearProps`/transform handling in GSAP 3.15; correct usage.

## 6. UX & Accessibility Audit — PASS

- **Reduced motion:** every module short-circuits; verified in browser (reduced-motion context → module inactive, content visible at natural opacity).
- **No invisible-content risk:** `.reveal[data-gsap-reveal]` neutralization in CSS means content is visible even if JS fails to load (CSS opacity 1 + `transition: none`); GSAP entrance begins from opacity 0 only after module init, and failure paths call `ctx.revert()`.
- **Mobile:** reduced movement (y 14 vs 24, smaller stagger) via matchMedia on all scroll-reveal pages.
- **No CLS from animations:** transform/opacity only; `clearProps` returns elements to natural state.
- **Page transitions:** entrance runs on every page load (Laravel full-page nav), 0.7 s duration, `power2.out` — fast, non-blocking, no `scroll-behavior` conflicts.
- **Focus/keyboard:** no animation blocks input; no `visibility:hidden` usage; `aria-hidden` untouched.
- **Legacy `main.js` observer** adds `.visible` to the same elements GSAP animates — harmless (CSS neutralization wins), but redundant (see §8).

## 7. Testing — PASS (435/435 + animation-specific)

- **Full Laravel suite:** `php artisan test` → **435 passed / 1344 assertions**.
- **Module smoke test** (`qa/phase6-smoke.mjs`, Node + GSAP in jsdom-like harness): all 8 Phase 6 modules PASS — expose, auto-init, duplicate-init guard, GSAP frames pump, destroy deactivates, re-init works. 0 FAIL.
- **Browser QA** (Playwright, real app on 127.0.0.1:8001, desktop 1440×900 / mobile 390×844 / reduced-motion contexts): all audited pages render, modules activate, no console/page errors, no invisible content, no overflow. Prior Phase 6 QA (4 scripts, 13 pages × 3 contexts) also all-clean.
- **One flaky pre-existing test observed** (`PerformancePolishTest::test_dashboard_timeline_preview_is_bounded`): failed once during a full-suite run, passes consistently in isolation (3×) and in subsequent full runs. Root cause: dashboard "Recent Activity" list is `latest('updated_at')->take(8)`; the test creates 15 memories in a tight loop with colliding timestamps, so tie-order is nondeterministic and sometimes includes memory 9/10. **Not caused by animation work** (reproduced only after a fresh `npm run build` — unrelated to edits; passes with and without the audit fixes). Recommend a follow-up: add a stable secondary sort (`latest('updated_at')` tie-breaker by id) or loosen the assertion. Left untouched per audit scope.

## 8. Defects Found & Fixed (evidence-based)

| # | Defect | Evidence | Fix | Verification |
|---|---|---|---|---|
| 1 | **UTF-8 mojibake in user-visible text** — `resources/views/login.blade.php`, `register.blade.php`, `dashboard.blade.php`, `shared/create.blade.php`: em-dash encoded as `â€"` (bytes `C3 A2 E2 82 AC E2 80 9C/9D`) | Byte-level scan of all views; visible in `<title>`, anniversary line, share-page copy | Byte-safe replacement of the corrupted 8-byte sequence with proper em-dash `E2 80 94` (backups in `%TEMP%\opencode\audit-backup\`) | `git diff` shows only the intended byte changes; browser QA shows clean text; full suite 435/435 |
| 2 | **Dead `mm` variable** in `memories-show-animations.js` (declared + `mm.revert()` but never assigned; no matchMedia) | Full file read + grep | Removed dead code to match peer `auth-animations.js` | Rebuild OK; smoke 0 FAIL; browser QA: module active, content visible, reduced-motion respected |

**Files NOT touched (per audit constraints):** `public/js/auth.js`, `public/css/dashboard.css` — no regression found in either; the mojibake fix required no edits there (their dash characters were clean `E2 80 94`).

## 9. Technical Debt / Recommendations (deferred, not blockers)

1. **Legacy reveal system is fully redundant** — all 135 `.reveal` elements are GSAP-managed; `main.js` IntersectionObserver (`.reveal` → `.visible`) and `.reveal` base CSS are dead weight. Safe to remove in Phase 7 (after confirming no element relies on `.visible` transitions).
2. **Boilerplate consolidation** — extract a shared `createPageAnimationBase()` helper before adding future modules (see §3); do NOT retrofit existing 31 modules.
3. **Flaky dashboard test** — add id tie-breaker to `DashboardService::activity()` or fix assertion (pre-existing, unrelated to animations).
4. **Documentation** — consider a short `resources/js/ANIMATIONS.md` describing the module contract (init/destroy/isActive, reduced motion, matchMedia) so Phase 7 additions follow the same pattern.

## 10. Phase 7 Recommendation

Architecture is ready for Phase 7. Recommended priorities:
1. Remove legacy `main.js` reveal observer + `.reveal` CSS (debt #1) — frees ~40 lines and the CSS transition overhead.
2. Add the shared animation-base helper for new modules only.
3. Fix the flaky dashboard test (debt #3).
4. Continue the established pattern for any new pages; no new engine needed — GSAP 3 + Lenis covers current and planned needs.

**Final verdict: PASS — no refactor required. Phase 7 may proceed.**