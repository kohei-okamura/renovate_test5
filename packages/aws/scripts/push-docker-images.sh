#!/bin/bash -e
# Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
# UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.

profile=$1
stage=$2
tag=$3

host=$(yarn --silent get-ecr-host -p "${profile}" | tail -n 3 | jq -r .host)
images=(
  "zinger/app-cli-${stage}"
  "zinger/app-server-${stage}"
  "zinger/web-${stage}"
)

for image in "${images[@]}"; do
  docker tag "${image}:latest" "${host}/${image}:${tag}"
  docker push "${host}/${image}:${tag}"
done
