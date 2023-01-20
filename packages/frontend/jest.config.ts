/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Config } from '@jest/types'
import deepmerge from 'deepmerge'
import path from 'path'
import base from '../../jest-base.config'

const projectDir = __dirname
const rootDir = path.resolve(projectDir, '..', '..')

const config: Config.InitialOptions = {
  collectCoverageFrom: [
    'main/**/*.{ts,vue}'
  ],
  coveragePathIgnorePatterns: [
    'main/middleware/',
    'main/mixins/',
    'main/models/',
    'main/plugins/',
    'main/types/'
  ],
  displayName: 'frontend',
  globalSetup: `${projectDir}/test/setup/global.js`,
  globals: {
    'ts-jest': { tsconfig: 'tsconfig-test.json' },
    '@vue/vue2-jest': { tsconfig: 'tsconfig-test.json' }
  },
  moduleFileExtensions: ['js', 'json', 'ts', 'vue'],
  moduleNameMapper: {
    '^~/(.+)$': `${projectDir}/main/$1`,
    '^~~/(.+)$': `${projectDir}/$1`
  },
  roots: [`${projectDir}/test`],
  setupFiles: [
    `${projectDir}/test/setup/avoid-console-info.ts`,
    `${projectDir}/test/setup/unhandled-promise-rejection.ts`
  ],
  setupFilesAfterEnv: [
    `${projectDir}/test/setup/matchers.ts`
  ],
  snapshotSerializers: [
    `${projectDir}/test/snapshot-serializer`
  ],
  testEnvironment: 'jsdom',
  testRegex: '\\.spec\\.ts$',
  transform: {
    '.+\\.(css|styl|less|sass|scss|svg|png|jpg|ttf|woff|woff2)$': 'jest-transform-stub',
    '^.*\\.js$': `${rootDir}/babel-jest.js`,
    '^.*\\.ts$': 'ts-jest',
    '^.*\\.vue$': '@vue/vue2-jest'
  },
  transformIgnorePatterns: [
    '.*/node_modules/(?!(jaco|vue-numeral-filter|vee-validate|vuetify)/).*/'
  ]
}
export default deepmerge<Config.InitialOptions>(base, config)
