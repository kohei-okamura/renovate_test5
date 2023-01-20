/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Config } from '@jest/types'
import deepmerge from 'deepmerge'
import base from './jest-base.config'

const config: Config.InitialOptions = {
  projects: [
    './jest-lint.config.ts',
    './packages/aws/jest.config.ts',
    './packages/frontend/jest.config.ts',
    './packages/service-code-api/jest.config.ts'
  ]
}
export default deepmerge<Config.InitialOptions>(base, config)
