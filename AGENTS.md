## Project Rules (wajib)

Before doing any work on this project, the agent MUST:

1. **Read these two files first** (they define the spec + execution plan):
   - `PRD_Manajemen_Kos_Kosan.md` — full requirements (PRD, scope, ERD, business rules, styling).
   - `ROADMAP_Checklist.md` — faze-by-faze execution checklist. Work follows this order.
2. **Use these skills** for every task:
   - **superpowers** (`using-superpowers` + relevant sub-skills like `brainstorming`, `systematic-debugging`, `test-driven-development`, `verification-before-completion`) — process skills first, then implementation.
   - **ponytail** — lazy/minimal by default: stdlib first, shortest working diff, no unrequested abstractions. Mark deliberate shortcuts with `ponytail:` comments.

Stack is locked (see ROADMAP "KEPUTUSAN TEKNIS FINAL"): PHP 8 procedural + MariaDB 10 + Apache + Podman Compose, Bootstrap 5 via CDN, NO emoji in UI (use Bootstrap Icons). Do not change these without explicit user approval.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

When the user types `/graphify`, use the installed graphify skill or instructions before doing anything else.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- Dirty graphify-out/ files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify. Only skip graphify if the task is about stale or incorrect graph output, or the user explicitly says not to use it.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
