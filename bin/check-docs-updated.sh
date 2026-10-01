#!/usr/bin/env bash
#
# Fails when a change touches the package's public surface but neither README.md nor docs/.
# Used by .github/workflows/docs-guard.yml; run it locally before opening a PR:
#
#   bin/check-docs-updated.sh origin/main HEAD
#
# Opt out for internal-only changes with the "skip-docs" PR label (SKIP_DOCS=true locally).

set -euo pipefail

base="${1:?usage: check-docs-updated.sh <base-ref> <head-ref>}"
head="${2:-HEAD}"

if [[ "${SKIP_DOCS:-false}" == "true" ]]; then
    echo "skip-docs: docs check skipped on purpose."
    exit 0
fi

changed="$(git diff --name-only "${base}...${head}")"

# Public surface: facade/classes, config keys, routes, migrations, Blade components.
surface="$(grep -E '^(src/|config/|routes/|database/migrations/|resources/views/components/)' <<< "${changed}" || true)"
docs="$(grep -E '^(README\.md|docs/)' <<< "${changed}" || true)"

if [[ -z "${surface}" ]]; then
    echo "No public-surface files changed: nothing to check."
    exit 0
fi

if [[ -n "${docs}" ]]; then
    echo "Public surface changed and docs were updated:"
    sed 's/^/  /' <<< "${docs}"
    exit 0
fi

echo "::error::This change touches the public surface but not README.md or docs/."
echo "Changed:"
sed 's/^/  /' <<< "${surface}"
echo
echo "Update README.md and/or the relevant docs/ page in this PR (see .github/CONTRIBUTING.md,"
echo "\"Documentation contract\"). If the change is internal only, add the \"skip-docs\" label."
exit 1
