/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { range } from '@zinger/helpers'

export function useRateOptions (min: number = 0, max: number = 10) {
  const rateOptions = range(min, max).map(x => ({ value: x, text: `${x}割` }))
  return { rateOptions }
}
