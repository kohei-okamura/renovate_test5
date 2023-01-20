#!/usr/bin/env node
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import 'source-map-support/register'
import { Zinger } from '~aws/bin/zinger'
import { ZingerStagingProps } from '~aws/bin/zinger-staging-props'

new Zinger(ZingerStagingProps)
