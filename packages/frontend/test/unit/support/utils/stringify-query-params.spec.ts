/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { stringifyQueryParams } from '~/support/utils/stringify-query-params'

describe('support/utils/stringify-query-params', () => {
  it('should stringify query params', () => {
    const stringValue = '田中将大'
    const emptyStringValue = ''
    const numberValue = 1234
    const zeroNumberValue = 0
    const trueValue = true
    const falseValue = false
    const nullValue = null
    const undefinedValue = undefined
    const createArrayValue = (): any[] => [
      stringValue,
      emptyStringValue,
      numberValue,
      zeroNumberValue,
      trueValue,
      falseValue,
      nullValue,
      undefinedValue
    ]
    const createObjectValue = () => ({
      stringValue,
      emptyStringValue,
      numberValue,
      zeroNumberValue,
      trueValue,
      falseValue,
      nullValue,
      undefinedValue
    })
    const arrayValue = [...createArrayValue(), createArrayValue(), createObjectValue()]
    const objectValue = {
      ...createObjectValue(),
      nested: createObjectValue(),
      array: createArrayValue()
    }

    const actual = stringifyQueryParams({
      ...createObjectValue(),
      arrayValue,
      objectValue
    })

    expect(actual).toMatchSnapshot()
  })
})
