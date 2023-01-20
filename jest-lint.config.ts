/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Config } from '@jest/types'
import deepmerge from 'deepmerge'
import base from './jest-base.config'

const config: Config.InitialOptions = {
  displayName: 'lint',
  runner: 'jest-runner-eslint',
  testMatch: [
    '<rootDir>/packages/**/*.(js|ts|vue)',
    '<rootDir>/*.(js|ts)',
    '<rootDir>/.*.(js|ts)'
  ],
  testPathIgnorePatterns: [
    '/node_modules/',
    '/public/'
  ]
}
export default deepmerge<Config.InitialOptions>(base, config)
