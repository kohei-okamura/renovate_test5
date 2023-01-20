set php_version (command cat (dirname (status -f))/.php-version)
set phpbrew (command -p which phpbrew 2> /dev/null || command which phpbrew)
eval (command $phpbrew env php-$php_version)
__phpbrew_set_path
phpbrew use
