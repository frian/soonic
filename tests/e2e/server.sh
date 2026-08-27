#!/usr/bin/env bash
set -euo pipefail

server_log="$(mktemp)"
response_body="$(mktemp)"

php -S 127.0.0.1:9810 -t public tests/e2e/router.php >"$server_log" 2>&1 &
server_pid=$!

cleanup() {
  kill "$server_pid" 2>/dev/null || true
  wait "$server_pid" 2>/dev/null || true
  rm -f "$server_log" "$response_body"
}
trap cleanup EXIT

for _ in {1..20}; do
  status="$(curl --silent --show-error --output "$response_body" --write-out '%{http_code}' http://127.0.0.1:9810/ || true)"

  if [[ "$status" =~ ^[23] ]]; then
    wait "$server_pid"
    exit $?
  fi

  if [[ "$status" != "000" ]]; then
    echo "E2E server readiness probe returned HTTP $status" >&2
    head -c 4000 "$response_body" >&2 || true
    printf '\n--- PHP server log ---\n' >&2
    cat "$server_log" >&2 || true
    exit 1
  fi

  sleep 0.5
done

echo "E2E server did not accept HTTP connections" >&2
cat "$server_log" >&2 || true
exit 1
