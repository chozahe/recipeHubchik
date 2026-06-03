#!/usr/bin/env bash
set -euo pipefail

image="${RECIPEHUB_IMAGE:-recipehub-app:latest}"
project="recipehub-ci-${GITHUB_RUN_ID:-local}-${GITHUB_RUN_ATTEMPT:-1}"
project="${project//[^a-zA-Z0-9_-]/-}"
compose_args=(--project-name "${project}" -f compose.yaml)

cleanup() {
  docker compose "${compose_args[@]}" down -v --remove-orphans >/dev/null 2>&1 || true
}

print_logs() {
  echo "--- docker compose ps ---"
  docker compose "${compose_args[@]}" ps || true
  echo "--- app logs ---"
  docker compose "${compose_args[@]}" logs app || true
  echo "--- postgres logs ---"
  docker compose "${compose_args[@]}" logs postgres || true
}

trap cleanup EXIT

echo "Starting RecipeHub smoke test with image: ${image}"
RECIPEHUB_IMAGE="${image}" docker compose "${compose_args[@]}" up -d postgres app

for attempt in {1..60}; do
  status="$(curl --silent --output /dev/null --write-out '%{http_code}' http://localhost:8080/ || true)"
  case "${status}" in
    2*|3*)
      echo "RecipeHub smoke test passed with HTTP ${status}."
      exit 0
      ;;
  esac

  if ! docker compose "${compose_args[@]}" ps app --status running | grep -q app; then
    echo "RecipeHub app container stopped before becoming healthy."
    print_logs
    exit 1
  fi

  echo "Waiting for RecipeHub HTTP response, attempt ${attempt}/60, last status: ${status}."
  sleep 5
done

echo "RecipeHub smoke test failed: application did not return HTTP 2xx/3xx."
print_logs
exit 1
