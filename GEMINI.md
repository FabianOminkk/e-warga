# High-Craft Frontend & Web Interface Standards (Anti-AI Template)

Adopted from `vercel-labs/agent-skills` (Web Design Guidelines, Writing Guidelines, Composition Patterns, React & Frontend Best Practices).
Strictly enforce these principles across all UI development, styling, and copywriting to eliminate the generic "AI template" look and deliver artisanal, production-grade craftsmanship.

## 1. Eliminate the "Generic AI Template" Feel
- **No Marketing Fluff & Banned Words:** Never use empty marketing adjectives (`mudah`, `simple`, `quick`, `solusi terbaik`, `sangat`, `fitur canggih`). Replace them with literal, concrete specifications, exact figures, and real workflows (e.g. "Persetujuan 1-klik berstempel QR resmi", "Integrasi sensor ESP32 RFID 13.56 MHz").
- **No AI Transitional Boilerplate:** Avoid patronizing conversational filler ("Dengan konfigurasi ini…", "Sekarang kita beralih ke…", "Berikut adalah langkah…"). State the fact, render the control, or take the action immediately with high signal-to-noise ratio.
- **Active Voice & Imperative Verbs:** Use direct imperative language for actions and controls ("Buka Gerbang", "Unduh Laporan Kas", "Simpan Data").

## 2. Web Interface & Interaction Design
- **Visible Focus Replacement:** NEVER leave `outline-none` / `outline: none` without an explicit focus replacement. Always provide `focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900`. Use `:focus-visible` rather than `:focus` so mouse clicks don't produce awkward rings.
- **Explicit Transitions (No `transition: all`):** Never use `transition: all` or `transition`. Always declare explicit transition properties (`transition-transform`, `transition-colors`, `transition-opacity`, `transition-shadow`) to maintain 60/120 FPS compositor performance.
- **Animation & Motion:** Respect user accessibility by honoring `prefers-reduced-motion` (`motion-reduce:transition-none`, `motion-reduce:animate-none`). Only animate compositor-friendly properties (`transform` and `opacity`).
- **Typography & Formatting Polish:**
  - Never use three raw periods `...`. Always use the genuine typographical horizontal ellipsis `…` (`&hellip;` / `…`).
  - Use `tabular-nums` on all numbers, counters, currency displays, timestamps, and data tables to prevent character-width jitter during live updates.
  - Apply `text-wrap: balance` or `text-pretty` to headlines (`h1`–`h4`) and cards to eliminate awkward single-word orphans/widows.
  - Use non-breaking spaces for badges, shortcuts, and units (`10&nbsp;MB`, `1-Klik`).
- **Touch & Mobile Ergonomics:**
  - Apply `touch-action: manipulation` to interactive touch targets to eliminate mobile 300ms double-tap delay.
  - Set `-webkit-tap-highlight-color: transparent`.
  - Apply `overscroll-behavior: contain` on modals, bottom sheets, and slide-over drawers to prevent scroll chaining to the background page.
- **Forms & Inputs:**
  - Always provide meaningful `autocomplete`, `name`, and input types (`email`, `tel`, `number`, `url`).
  - Add `spellcheck="false"` on usernames, search inputs, token codes, and identification fields.
  - Input placeholders must end with `…` (e.g., `Cari warga atau alamat rumah…`).
  - Never prevent user paste (`onPaste` with `preventDefault`).

## 3. Theming & Dark Mode
- Include `color-scheme: light dark` in root/html so native browser scrollbars, inputs, and system popups adhere cleanly to the active theme.
- Ensure `<meta name="theme-color">` dynamically harmonizes with light/dark page backgrounds.


<auto-deploy-workflow>
# Automatic Web Sync & Anti-Conflict Policy
- **Finalized Firmware Protection:** NEVER modify or commit `firmware/*.ino` files unless the user explicitly asks to edit firmware. Always exclude `firmware/` and `scratch/` when staging commits (`git add -A -- . ':!scratch' ':!firmware' ':!*.log'`).
- **Auto-Sync to Live Web:** Whenever a coding task or bugfix in this workspace is completed AND passes verification (`php -l`, `npm run build`, and regression/checklist checks), automatically sync changes to production via `scripts/auto-sync-web.ps1` (or `git fetch origin main` -> `git commit` -> `git pull --rebase --autostash origin main` -> `git push origin main`), which triggers GitHub Actions `.github/workflows/deploy.yml` to deploy automatically to `e-warga.unique-monsters.my.id`.
- **Conflict Prevention:** Never force-push (`--force`). Always rebase/sync with `origin/main` before pushing, and never push unverified or half-finished code edits.
</auto-deploy-workflow>

<mandatory-quality-audit>
# Mandatory Double-Checking, Zero-Conflict & Bug-Hunter Policy
- **Mandatory Double-Checking:** For any troubleshooting, feature update, or refactor, double-check all connected layers (Web/API routes, controllers, models/migrations, desktop & mobile Blade views, tests, and CI/CD workflow) before finishing or reporting.
- **Zero-Conflict Validation:** Check `git fetch origin main`, `git diff`, route names, database columns, and existing test/UI assertions so nothing collides or breaks existing behavior.
- **Full Codebase Checkup (No Missed Side Effects):** Always verify after the final edit with `php -l`, `php artisan view:cache` / `route:cache`, `npm run build` (when frontend files change), and the verification suites (`verify_all_checklist.php`, `verify_regression_extra.php`, `verify_uiux.php`, `verify_rfid.php`, and CI `php artisan test`).
- **Proactive Bug Hunting:** Actively hunt and fix potential bugs before pushing or reporting — including RBAC/authorization gaps, `Undefined array key` on optional/nullable validated inputs, database column/enum mismatches, IoT offline/online state integrity, and mobile responsiveness.
</mandatory-quality-audit>
