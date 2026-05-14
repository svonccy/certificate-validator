# Architectural Rules - CNSM Project
- Stack: Laravel 11, PHP 8.5+, FilamentPHP v5.
- Microservice: Python 3 CLI script with `pyHanko` for PAdES signatures.
- FORBIDDEN to use `shell_exec()` in PHP. ALWAYS use `Symfony\Component\Process\Process` to call Python.
- PDF manipulation in PHP: use `setasign/fpdi` and `tecnickcom/tcpdf`.
- Conventions: Strict typing in PHP (`declare(strict_types=1);`).
- Language: Variables, methods, database tables in Spanish (e.g., `$certificado`, `generarQr()`).