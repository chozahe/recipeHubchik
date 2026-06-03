#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${TELEGRAM_BOT_TOKEN:-}" || -z "${TELEGRAM_CHAT_ID:-}" ]]; then
  echo "Telegram notification skipped: TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID is not configured."
  exit 0
fi

status="${NOTIFY_STATUS:-unknown}"
image="${NOTIFY_IMAGE:-}"
extra_message="${NOTIFY_EXTRA_MESSAGE:-}"
server_url="${GITHUB_SERVER_URL:-https://github.com}"
repository="${GITHUB_REPOSITORY:-unknown/repository}"
run_id="${GITHUB_RUN_ID:-}"
run_url="${server_url}/${repository}/actions/runs/${run_id}"
sha="${GITHUB_SHA:-unknown}"
short_sha="${sha:0:7}"

message="RecipeHub CI/CD
Status: ${status}
Ref: ${GITHUB_REF_NAME:-unknown}
Commit: ${short_sha}
Actor: ${GITHUB_ACTOR:-unknown}
Event: ${GITHUB_EVENT_NAME:-unknown}"

if [[ -n "${image}" ]]; then
  message+=$'\n'"Image: ${image}"
fi

if [[ -n "${extra_message}" ]]; then
  message+=$'\n'"Message: ${extra_message}"
fi

message+=$'\n'"Run: ${run_url}"

if curl --fail --show-error --silent \
  --request POST \
  --data-urlencode "chat_id=${TELEGRAM_CHAT_ID}" \
  --data-urlencode "text=${message}" \
  "https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage" >/dev/null; then
  echo "Telegram notification sent: ${status}."
else
  echo "Telegram notification failed, continuing pipeline."
fi
