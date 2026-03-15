#!/usr/bin/env bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"
BUILD_DIR="$PROJECT_DIR/build"
OUTPUT_DIR="$BUILD_DIR/clienvy-connect"

rm -rf "$BUILD_DIR"
mkdir -p "$OUTPUT_DIR"

rsync -av \
  --exclude=".git" \
  --exclude=".claude" \
  --exclude=".github" \
  --exclude=".idea" \
  --exclude="node_modules" \
  --exclude="tests" \
  --exclude=".DS_Store" \
  --exclude=".env" \
  --exclude=".env.*" \
  --exclude="build" \
  "$PROJECT_DIR/" "$OUTPUT_DIR/"

cd "$BUILD_DIR"
zip -r clienvy-connect.zip clienvy-connect
