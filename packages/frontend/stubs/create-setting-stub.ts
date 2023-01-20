/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Setting } from '~/models/setting'
import { SettingApi } from '~/services/api/setting-api'
import { createFaker } from '~~/stubs/fake'
import { ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const SETTING_ID_MAX = ID_MAX
export const SETTING_ID_MIN = ID_MIN

export function createSettingStub (): Setting {
  const faker = createFaker(SEEDS[SETTING_ID_MIN])
  return {
    bankingClientCode: faker.randomNumericString(10),
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createSettingResponseStub (): SettingApi.GetResponse {
  return {
    organizationSetting: {
      ...createSettingStub()
    }
  }
}
