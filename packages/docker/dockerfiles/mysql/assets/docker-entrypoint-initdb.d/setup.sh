#!/bin/bash

#
# Create databases and users.
#
# See https://github.com/docker-library/mariadb/blob/master/10.4/docker-entrypoint.sh#L93
#
${mysql[@]} << EOS
  CREATE DATABASE IF NOT EXISTS zinger CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
  CREATE DATABASE IF NOT EXISTS zinger_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
  CREATE USER zinger IDENTIFIED BY '${MYSQL_ZINGER_PASSWORD}';
  GRANT ALL PRIVILEGES ON zinger.* TO 'zinger'@'%';
  GRANT ALL PRIVILEGES ON zinger_testing.* TO 'zinger'@'%';
EOS
