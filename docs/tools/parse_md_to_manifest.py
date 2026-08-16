"""
tools/parse_md_to_manifest.py

- Scans a directory of Markdown files (defaults to `manifests_md/`).
- For each file, extracts data from YAML front matter (preferred) or top-level "Key: value" lines.
- Emits a JSON manifest with the same basename into an output directory (defaults to `manifests/`).
- With --check, compares generated manifests to committed ones and exits non-zero if any differ.
"""
from __future__ import annotations
import argparse
import json
import os
import re
import sys
from pathlib import Path

try:
    import yaml
except Exception:
    yaml = None  # We'll give a helpful error below if YAML is needed

FRONT_MATTER_RE = re.compile(r"^---\s*$", re.MULTILINE)

def parse_yaml_front_matter(text: str):
    parts = FRONT_MATTER_RE.split(text, maxsplit=2)
    if len(parts) >= 3 and parts[0].strip() == "":
        # parts: '', yaml, rest
        yaml_text = parts[1]
        if yaml is None:
            raise RuntimeError("PyYAML is required to parse YAML front matter. Install pyyaml.")
        return yaml.safe_load(yaml_text)
    return None

def parse_key_values_header(text: str):
    """
    Parse simple "Key: value" pairs at start of file until empty line.
    """
    data = {}
    for line in text.splitlines():
        if not line.strip():
            break
        if ":" in line:
            k, v = line.split(":", 1)
            data[k.strip()] = v.strip()
        else:
            break
    return data or None

def generate_manifest(md_path: Path) -> dict:
    text = md_path.read_text(encoding="utf-8")
    data = parse_yaml_front_matter(text)
    if data is not None:
        return data
    data = parse_key_values_header(text)
    if data is not None:
        return data
    # Fallback: include the whole content as "description"
    return {"description": text.strip()}

def write_manifest(manifest: dict, out_path: Path):
    out_path.parent.mkdir(parents=True, exist_ok=True)
    out_path.write_text(json.dumps(manifest, indent=2, sort_keys=True) + "\n", encoding="utf-8")

def main(argv=None):
    p = argparse.ArgumentParser(description="Parse Markdown files into JSON manifests.")
    p.add_argument("--source", "-s", default="manifests_md", help="Source directory with .md files")
    p.add_argument("--out", "-o", default="manifests", help="Output directory for generated manifests (JSON)")
    p.add_argument("--ext", default=".json", help="Output file extension (default: .json)")
    p.add_argument("--check", action="store_true", help="Compare generated manifests with committed ones and exit non-zero if differences exist")
    p.add_argument("--glob", default="**/*.md", help="Glob pattern to find markdown files under source")
    args = p.parse_args(argv)

    src = Path(args.source)
    out = Path(args.out)
    if not src.exists():
        print(f"Source directory {src} does not exist. Nothing to do.", file=sys.stderr)
        return 1

    failures = 0
    changed_files = []

    for md_file in src.glob(args.glob):
        if md_file.is_dir():
            continue
        try:
            manifest = generate_manifest(md_file)
        except Exception as exc:
            print(f"ERROR parsing {md_file}: {exc}", file=sys.stderr)
            failures += 1
            continue

        out_file = out / md_file.with_suffix(args.ext).name
        # Ensure stable ordering for comparison
        new_content = json.dumps(manifest, indent=2, sort_keys=True) + "\n"

        if args.check and out_file.exists():
            old_content = out_file.read_text(encoding="utf-8")
            if old_content != new_content:
                print(f"DIFFER: {out_file} differs from generated output", file=sys.stderr)
                changed_files.append(str(out_file))
        else:
            write_manifest(manifest, out_file)

    if args.check:
        if changed_files:
            print("Manifests out-of-date for the following files:", file=sys.stderr)
            for f in changed_files:
                print(f"  - {f}", file=sys.stderr)
            return 2
        else:
            print("All manifests are up-to-date.")
            return 0

    if failures:
        print(f"Completed with {failures} parsing failures.", file=sys.stderr)
        return 3

    print("Manifests generated successfully.")
    return 0

if __name__ == "__main__":
    raise SystemExit(main())
