/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { UserDwsSubsidyId } from '~/models/user-dws-subsidy'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { createDwsSubsidyStub, DWS_SUBSIDY_ID_MIN } from '~~/stubs/create-dws-subsidy-stub'

export function createDwsSubsidyResponseStub (
  id: UserDwsSubsidyId = DWS_SUBSIDY_ID_MIN,
  subsidyType: UserDwsSubsidyType = UserDwsSubsidyType.benefitRate
): DwsSubsidiesApi.GetResponse {
  return {
    dwsSubsidy: createDwsSubsidyStub(id, subsidyType)
  }
}
