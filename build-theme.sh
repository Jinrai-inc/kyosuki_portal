#!/usr/bin/env bash
# Build a WordPress-installable zip for the Kyosuki Pop theme.
# Result: dist/kyosuki-pop.zip  (top-level directory inside the zip is "kyosuki-pop/")
set -euo pipefail

THEME_SLUG="kyosuki-pop"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DIST_DIR="$ROOT_DIR/dist"
STAGE_DIR="$(mktemp -d)"
TARGET_DIR="$STAGE_DIR/$THEME_SLUG"

cleanup() { rm -rf "$STAGE_DIR"; }
trap cleanup EXIT

mkdir -p "$DIST_DIR" "$TARGET_DIR"

# Copy everything (including dotfiles) to the staging dir
cp -R "$ROOT_DIR/." "$TARGET_DIR/"

# Strip ignored entries from the staging copy using .distignore
if [[ -f "$ROOT_DIR/.distignore" ]]; then
  while IFS= read -r pattern; do
    [[ -z "$pattern" || "$pattern" =~ ^# ]] && continue
    find "$TARGET_DIR" -depth -name "$pattern" -exec rm -rf {} + 2>/dev/null || true
  done < "$ROOT_DIR/.distignore"
fi

ZIP_PATH="$DIST_DIR/$THEME_SLUG.zip"
rm -f "$ZIP_PATH"
( cd "$STAGE_DIR" && zip -rq "$ZIP_PATH" "$THEME_SLUG" )

echo "Built: $ZIP_PATH"
