#!/bin/bash -e
# Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
# UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.

op get document zinger.ts > "$(dirname $0)/ssm-parameters/zinger.ts"
op get document zinger-staging.ts > "$(dirname $0)/ssm-parameters/zinger-staging.ts"
op get document zinger-sandbox.ts > "$(dirname $0)/ssm-parameters/zinger-sandbox.ts"
