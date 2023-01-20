/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { RolesApi } from '~/services/api/roles-api'
import { createRoleStubs } from '~~/stubs/create-role-stub'

export function createRoleIndexResponseStub (): RolesApi.GetIndexResponse {
  const list = createRoleStubs()
  return {
    list,
    pagination: {
      desc: false,
      page: 1,
      pages: 1,
      itemsPerPage: list.length,
      sortBy: 'sortOrder',
      count: list.length
    }
  }
}
