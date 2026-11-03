#!/usr/bin/env bash
#
# One-command release helper for Boligkalkulator.
#
# Bumps the version (plugin header + BOLIGKALKULATOR_VERSION constant), commits the
# bump, then creates and pushes a git tag. The GitHub Actions "Release" workflow
# builds the install zip and publishes the GitHub Release (stable or
# pre-release, decided by the tag name). The in-plugin updater then offers it in
# wp-admin.
#
# Usage:
#   scripts/release.sh 1.1.0          # stable release  -> rolls out to sites
#   scripts/release.sh 1.1.0-beta.1   # pre-release     -> beta/test sites only
#
set -euo pipefail

if [ "$#" -ne 1 ]; then
  echo "Usage: scripts/release.sh <version>   e.g. 1.1.0 or 1.1.0-beta.1" >&2
  exit 1
fi

VERSION="$1"
CORE="${VERSION%%-*}"   # strip pre-release suffix for the file headers
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [ -n "$(git status --porcelain)" ]; then
  echo "Working tree is not clean. Commit or stash changes first." >&2
  exit 1
fi

FILE="boligkalkulator.php"

echo "==> Bumping version to $CORE"
sed -i '' -E "s/^( \* Version:[[:space:]]*).*/\1${CORE}/" "$FILE"
sed -i '' -E "s/(define\( *'BOLIGKALKULATOR_VERSION', *')[^']*(' *\);)/\1${CORE}\2/" "$FILE"

echo "==> Verifying"
grep -m1 'Version:' "$FILE"
grep -m1 'BOLIGKALKULATOR_VERSION' "$FILE"

echo "==> Committing and tagging v${VERSION}"
git add "$FILE"
git commit -q -m "Release v${VERSION}"
git tag "v${VERSION}"

echo "==> Pushing"
git push origin HEAD
git push origin "v${VERSION}"

echo "==> Done. GitHub Actions will build the zip and publish the release."
if echo "$VERSION" | grep -q '-'; then
  echo "    This is a PRE-RELEASE (beta) — only beta/test sites will receive it."
else
  echo "    This is a STABLE release — it will roll out to all sites."
fi
