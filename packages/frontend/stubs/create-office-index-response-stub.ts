/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficesApi } from '~/services/api/offices-api'
import { createIndexResponse } from '~~/stubs/create-index-response'
import { createOfficeStubs, OFFICE_STUB_COUNT } from '~~/stubs/create-office-stub'

export function createOfficeIndexResponseStub (params: OfficesApi.GetIndexParams = {}): OfficesApi.GetIndexResponse {
  return createIndexResponse(params, OFFICE_STUB_COUNT, createOfficeStubs)
}
