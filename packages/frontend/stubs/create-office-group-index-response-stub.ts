/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'

export function createOfficeGroupIndexResponseStub (): OfficeGroupsApi.GetIndexResponse {
  const list = createOfficeGroupStubs()
  return {
    list,
    pagination: {
      desc: false,
      page: 1,
      pages: 1,
      itemsPerPage: list.length,
      sortBy: '',
      count: list.length
    }
  }
}
