/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { isEmpty } from '@zinger/helpers/index'
import { mapValues } from '~/support/utils/map-values'

export const createParamsToQuery = (params: Record<string, any>) => {
  return mapValues(params, x => {
    if (isEmpty(x)) {
      return ''
    } else if (Array.isArray(x)) {
      return x.map(x => String(x))
    }
    return String(x)
  })
}
