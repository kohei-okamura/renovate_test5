/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { OfficeGroupId } from '~/models/office-group'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'
import { createOfficeGroupStub } from '~~/stubs/create-office-group-stub'

export function createOfficeGroupResponseStub (id: OfficeGroupId): OfficeGroupsApi.GetResponse {
  return {
    officeGroup: createOfficeGroupStub(id)!
  }
}
