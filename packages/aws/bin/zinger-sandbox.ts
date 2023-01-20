#!/usr/bin/env node
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import 'source-map-support/register'
import { Zinger } from '~aws/bin/zinger'
import { ZingerSandboxProps } from '~aws/bin/zinger-sandbox-props'

new Zinger(ZingerSandboxProps)
