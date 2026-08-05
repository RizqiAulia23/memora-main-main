---
name: performance-optimization
description: Optimize queries, eager load, cache when appropriate, profile hotspots.
---

# performance-optimization

## Mission
Optimize queries, eager load, cache when appropriate, profile hotspots.

## Workflow
1. Analyze existing code.
2. Explain the implementation plan.
3. Make minimal focused changes.
4. Verify with tests/lint where appropriate.
5. Summarize what changed.

## Laravel Rules
- Do not break existing UI.
- Use Form Requests for validation.
- Use Route Model Binding.
- Use Policies for authorization.
- Prefer Eloquent relationships.
- Keep controllers thin.
- Use Storage facade for uploads.
- Run Pint on touched files.
- Prefer readable, maintainable code.
