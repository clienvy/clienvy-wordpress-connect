#!/usr/bin/env bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"
BUILD_DIR="$PROJECT_DIR/build"
OUTPUT_DIR="$BUILD_DIR/clienvy-connect"

if ! command -v npm >/dev/null 2>&1; then
  echo "Error: npm is niet beschikbaar. Installeer Node.js om een release te bouwen." >&2
  exit 1
fi

cd "$PROJECT_DIR"
npm ci
npm run build

rm -rf "$BUILD_DIR"
mkdir -p "$OUTPUT_DIR"

rsync -av \
  --exclude=".git" \
  --exclude=".claude" \
  --exclude=".github" \
  --exclude=".idea" \
  --exclude="node_modules" \
  --exclude="src" \
  --exclude="tests" \
  --exclude=".DS_Store" \
  --exclude=".env" \
  --exclude=".env.*" \
  --exclude="build" \
  --exclude="build-release.sh" \
  --exclude=".gitignore" \
  --exclude="package.json" \
  --exclude="package-lock.json" \
  --exclude="vite.config.js" \
  "$PROJECT_DIR/" "$OUTPUT_DIR/"

cd "$BUILD_DIR"
zip -r clienvy-connect.zip clienvy-connect
