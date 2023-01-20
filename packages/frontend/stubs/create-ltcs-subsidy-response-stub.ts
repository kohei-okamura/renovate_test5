/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserLtcsSubsidyId } from '~/models/user-ltcs-subsidy'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'
import { createLtcsSubsidyStub, LTCS_SUBSIDY_ID_MIN } from '~~/stubs/create-ltcs-subsidy-stub'

export function createLtcsSubsidyResponseStub (
  id: UserLtcsSubsidyId = LTCS_SUBSIDY_ID_MIN
): LtcsSubsidiesApi.GetResponse {
  return {
    ltcsSubsidy: createLtcsSubsidyStub(id)
  }
}
