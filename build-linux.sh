#!/bin/bash
set -e

SRC="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="$HOME/.cache/movieshelf-linux-build"

echo "→ Quellen synchronisieren..."
rsync -a --delete \
  --exclude node_modules \
  --exclude release \
  --exclude dist \
  --exclude dist-electron \
  "$SRC/" "$BUILD_DIR/"

echo "→ Abhängigkeiten installieren..."
cd "$BUILD_DIR"
npm install --prefer-offline
npx @electron/rebuild -f -w better-sqlite3

echo "→ Linux-Build starten..."
npm run electron:build

echo "→ AppImage zurückkopieren..."
mkdir -p "$SRC/release"
cp "$BUILD_DIR"/release/*.AppImage "$SRC/release/" 2>/dev/null || true
cp "$BUILD_DIR"/release/*.deb      "$SRC/release/" 2>/dev/null || true

echo "✓ Fertig! Release: $SRC/release/"
ls "$SRC/release/"
