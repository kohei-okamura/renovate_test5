/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsVersion, LtcsVersion } from '../constants'

const date202210 = new Date(2022, 9, 1, 0, 0, 0, 0)
const date202104 = new Date(2021, 3, 1, 0, 0, 0, 0)

export const determineVersion = (input: Date | string): DwsVersion | LtcsVersion => {
  const date = typeof input === 'string' ? new Date(`${input}-01`) : input
  if (date >= date202210) {
    return 202210
  } else if (date >= date202104) {
    return 202104
  } else {
    return 201910
  }
}
