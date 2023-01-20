#!/usr/bin/env node
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { setupDwsHomeHelpServiceDb } from '../lib/app/setup-dws-home-help-service-db'
import { setupDwsVisitingCareForPwsdDb } from '../lib/app/setup-dws-visiting-care-for-pwsd-service-db'
import { setupLtcsHomeVisitLongTermCareDb } from '../lib/app/setup-ltcs-home-visit-long-term-care-db'

const main = () => Promise.all([
  setupDwsHomeHelpServiceDb(),
  setupDwsVisitingCareForPwsdDb(),
  setupLtcsHomeVisitLongTermCareDb()
])

// noinspection JSIgnoredPromiseFromCall
main()
