# CNSM Certificate Validator - Copilot Instructions

## Project Summary (Source of Truth)
- Sources of truth: [README.md](README.md) and [docs/sequence_diagram.mmd](docs/sequence_diagram.mmd).
- System: two-pass PDF flow (draft QR, then signed validation).
- Stack: Laravel 13 (PHP 8.3+), FilamentPHP v5, MySQL.
- PDF: generate/modify with `setasign/fpdi` + `tecnickcom/tcpdf`.
- Crypto validation: Python 3 CLI using `pyHanko` for PAdES.
- Follow Standard Code principles and Laravel best practices.

## Non-Negotiable Rules
- FORBIDDEN: `shell_exec()` in PHP.
- ALWAYS use `Symfony\Component\Process\Process` for Python orchestration.
- PHP must use strict typing: `declare(strict_types=1);`.
- Naming in Spanish for variables, methods, and DB tables (e.g., `$certificado`, `generarQr()`).

## AI/Agent Setup
- This repo uses GitHub Copilot as the only assistant. Prefer MCP tools when available.
- Copilot MCP config lives in `.vscode/mcp.json` (installed via Laravel Boost).
- Skills live in `.github/skills/` and can be invoked when relevant.

## Filament v5 vs v4 Warning (Important)
- Local skill `filament-specialist` is aligned to Filament v4 and may be outdated.
- If Filament guidance seems wrong or causes errors, treat it as a version mismatch.
- Always verify against official Filament v5 docs when working on Filament code.

## Workflow Expectations
- Follow the two-pass PDF flow from the README and the sequence diagram when designing features.
- Prefer official Laravel/Filament conventions and avoid unnecessary custom patterns.