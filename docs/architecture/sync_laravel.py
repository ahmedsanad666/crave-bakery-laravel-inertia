#!/usr/bin/env python3
"""
Laravel-safe architecture sync for Crave Bakery.

Do NOT run `python docs/architecture/build_html.py --sync` on this project.
The upstream scanner mis-detects FastAPI and wipes the Laravel manifest.

Usage (from project root):
    python docs/architecture/sync_laravel.py
"""

from __future__ import annotations

import os
import re
import shutil
import subprocess
import sys
from pathlib import Path

ARCH = Path(__file__).resolve().parent
ROOT = ARCH.parents[1]
ROUTES = ARCH / "_routes_raw.json"


def _php_version(php_bin: str) -> tuple[int, int, int] | None:
    try:
        out = subprocess.run(
            [php_bin, "-r", "echo PHP_VERSION;"],
            capture_output=True,
            check=False,
            text=True,
        )
        if out.returncode != 0:
            return None
        match = re.match(r"(\d+)\.(\d+)\.(\d+)", out.stdout.strip())
        if not match:
            return None
        return tuple(int(part) for part in match.groups())  # type: ignore[return-value]
    except OSError:
        return None


def resolve_php() -> str:
    """Prefer PHP >= 8.4 (Composer platform requirement for this app)."""
    candidates: list[str] = []

    # Explicit Herd shims first (Windows often also has older XAMPP php.exe).
    herd = Path.home() / ".config" / "herd" / "bin"
    for name in ("php84.bat", "php83.bat", "php.bat"):
        candidate = herd / name
        if candidate.is_file():
            candidates.append(str(candidate))

    which = shutil.which("php")
    if which:
        candidates.append(which)

    xampp = Path(r"C:\xampp\php\php.exe")
    if xampp.is_file():
        candidates.append(str(xampp))

    seen: set[str] = set()
    best: tuple[str, tuple[int, int, int]] | None = None
    for candidate in candidates:
        key = candidate.lower()
        if key in seen:
            continue
        seen.add(key)
        version = _php_version(candidate)
        if not version:
            continue
        if best is None or version > best[1]:
            best = (candidate, version)

    if not best:
        raise RuntimeError("No usable PHP binary found on PATH.")

    php_bin, version = best
    if version < (8, 4, 0):
        raise RuntimeError(
            f"Found PHP {'.'.join(map(str, version))} at {php_bin}, "
            "but this project requires PHP >= 8.4.1 (Laravel Herd php84 recommended)."
        )

    print(f"[crave-arch] Using PHP {'.'.join(map(str, version))}: {php_bin}")
    return php_bin


def main() -> int:
    env = os.environ.copy()
    env["PYTHONIOENCODING"] = "utf-8"
    env["PYTHONUTF8"] = "1"

    try:
        php_bin = resolve_php()
    except RuntimeError as exc:
        print(f"[crave-arch] {exc}", file=sys.stderr)
        if ROUTES.is_file():
            print("[crave-arch] Falling back to existing _routes_raw.json", file=sys.stderr)
        else:
            return 1
        php_bin = None

    if php_bin:
        print("[crave-arch] Exporting Laravel routes...")
        result = subprocess.run(
            [php_bin, "artisan", "route:list", "--json"],
            cwd=ROOT,
            capture_output=True,
            check=False,
        )
        if result.returncode != 0:
            sys.stderr.write(result.stderr.decode("utf-8", errors="replace"))
            if not ROUTES.is_file():
                print("[crave-arch] Failed to export routes.", file=sys.stderr)
                return result.returncode
            print("[crave-arch] Artisan failed; reusing existing _routes_raw.json", file=sys.stderr)
        else:
            # Artisan JSON is UTF-8; write bytes directly to avoid PowerShell UTF-16.
            ROUTES.write_bytes(result.stdout)

    if not ROUTES.is_file():
        print("[crave-arch] Missing route export. Cannot build manifest.", file=sys.stderr)
        return 1

    print("[crave-arch] Building Laravel architecture.json...")
    subprocess.run([sys.executable, str(ARCH / "_build_manifest.py")], cwd=ROOT, check=True, env=env)

    print("[crave-arch] Regenerating architecture.html...")
    subprocess.run([sys.executable, str(ARCH / "build_html.py")], cwd=ROOT, check=True, env=env)

    print(f"[crave-arch] Done. Open {ARCH / 'architecture.html'}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
