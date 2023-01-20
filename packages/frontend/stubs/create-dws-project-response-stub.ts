/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsProjectId } from '~/models/dws-project'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createDwsProjectStub } from '~~/stubs/create-dws-project-stub'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'

export const DWS_PROJECT_ID_MIN = USER_ID_MIN * 10

export function createDwsProjectResponseStub (id: DwsProjectId = DWS_PROJECT_ID_MIN): DwsProjectsApi.GetResponse {
  const contract = createContractStub(id)
  return {
    dwsProject: createDwsProjectStub(id, contract)
  }
}
