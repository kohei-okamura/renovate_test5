/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { toBeArray } from '~~/test/matchers/to-be-array'
import { toBeDisabled } from '~~/test/matchers/to-be-disabled'
import { toBeEmptyArray } from '~~/test/matchers/to-be-empty-array'
import { toBeFalse } from '~~/test/matchers/to-be-false'
import { toBeFunction } from '~~/test/matchers/to-be-function'
import { toBePassed } from '~~/test/matchers/to-be-passed'
import { toBeRef } from '~~/test/matchers/to-be-ref'
import { toBeRefTo } from '~~/test/matchers/to-be-ref-to'
import { toBeTrue } from '~~/test/matchers/to-be-true'
import { toContainElement } from '~~/test/matchers/to-contain-element'
import { toExist } from '~~/test/matchers/to-exist'

expect.extend({
  toBeArray,
  toBeDisabled,
  toBeEmptyArray,
  toBeFalse,
  toBeFunction,
  toBePassed,
  toBeRef,
  toBeRefTo,
  toBeTrue,
  toContainElement,
  toExist
})
