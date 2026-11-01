#!/usr/bin/env bash
# Deploys the theme or plugin to the WordPress site through the Novamira CLI.
# Usage: wordpress/deploy.sh plugin|theme
# Zips the folder, uploads it with a one-time upload link, and replaces the
# folder on the server. Tokens never appear in the output.
set -euo pipefail
export NODE_USE_ENV_PROXY=1 NODE_NO_WARNINGS=1
SITE="${EWIL_SITE:-eduwomenleaders.com}"
ROOT="$(cd "$(dirname "$0")" && pwd)"
case "${1:-}" in
  plugin) SRC="$ROOT/plugins/ewil-conference-core"; DEST="WP_PLUGIN_DIR"; NAME="ewil-conference-core" ;;
  theme)  SRC="$ROOT/themes/edu-women-leadership"; DEST="get_theme_root()"; NAME="edu-women-leadership" ;;
  *) echo "usage: $0 plugin|theme" >&2; exit 2 ;;
esac
TMP="$(mktemp -d)"; trap 'rm -rf "$TMP"' EXIT
(cd "$(dirname "$SRC")" && zip -qr "$TMP/$NAME.zip" "$NAME" -x '*.DS_Store')
REMOTE="wp-content/uploads/ewil-deploy/$NAME.zip"
novamira --site "$SITE" run novamira/create-upload-link --input "{\"path\":\"$REMOTE\",\"overwrite\":true,\"expires_in\":300}" --json 2>/dev/null > "$TMP/link.json"
python3 -I - "$TMP/link.json" "$TMP/$NAME.zip" <<'EOF'
import json, sys, subprocess
d = json.load(open(sys.argv[1]))["data"]
url = d.get("upload_url"); hdr = d.get("token_header"); tok = d.get("upload_token")
r = subprocess.run(["curl", "-sS", "-f", "-X", "PUT", "-H", f"{hdr}: {tok}", "--data-binary", "@" + sys.argv[2], url], capture_output=True, text=True)
if r.returncode: sys.exit("upload failed: " + r.stderr[:300])
print("uploaded", sys.argv[2].rsplit("/", 1)[-1])
EOF
PHP=$(cat <<EOF
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global \$wp_filesystem;
\$zip = ABSPATH . '$REMOTE';
\$dest = $DEST;
\$target = trailingslashit(\$dest) . '$NAME';
if (is_dir(\$target)) { \$wp_filesystem->delete(\$target, true); }
\$r = unzip_file(\$zip, \$dest);
wp_delete_file(\$zip);
if (is_wp_error(\$r)) { return 'ERROR: ' . \$r->get_error_message(); }
do_action('litespeed_purge_all');
if (function_exists('opcache_reset')) { opcache_reset(); }
return 'deployed to ' . \$target;
EOF
)
python3 -I -c 'import json,sys; print(json.dumps({"code": sys.argv[1]}))' "$PHP" > "$TMP/php.json"
novamira --site "$SITE" run novamira/execute-php --input "@$TMP/php.json" --json --yes 2>/dev/null | python3 -I -c 'import json,sys; d=json.load(sys.stdin); print(d["data"].get("return_value") if d.get("ok") else d)'
