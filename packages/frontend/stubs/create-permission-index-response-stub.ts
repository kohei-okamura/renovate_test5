/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { PermissionsApi } from '~/services/api/permissions-api'
import { createPermissionGroupStubs } from '~~/stubs/create-permission-group-stub'

export function createPermissionIndexResponseStub (): PermissionsApi.GetIndexResponse {
  const list = createPermissionGroupStubs()
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
