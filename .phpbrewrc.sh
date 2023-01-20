php_version="php-$(command cat "$(dirname "$0")/.php-version")"
phpbrew="$(command -p which phpbrew 2>/dev/null || command which phpbrew)"
eval "$(command "$phpbrew" env "$php_version")"
__phpbrew_set_path
phpbrew use
