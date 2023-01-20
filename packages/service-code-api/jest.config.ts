/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Config } from '@jest/types'
import deepmerge from 'deepmerge'
import path from 'path'
import base from '../../jest-base.config'

const projectDir = __dirname
const rootDir = path.resolve(projectDir, '..', '..')

const config: Config.InitialOptions = {
  displayName: 'service-code-api',
  globals: {
    'ts-jest': { tsconfig: 'tsconfig-test.json' }
  },
  moduleFileExtensions: ['js', 'json', 'ts'],
  roots: [`${projectDir}/test`],
  testRegex: '\\.spec\\.ts$',
  transform: {
    '^.*\\.js$': `${rootDir}/babel-jest.js`,
    '^.*\\.ts$': 'ts-jest'
  }
}
export default deepmerge<Config.InitialOptions>(base, config)
