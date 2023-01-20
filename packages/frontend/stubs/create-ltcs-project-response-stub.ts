/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsProjectId } from '~/models/ltcs-project'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'
import { createContractStub } from '~~/stubs/create-contract-stub'
import { createLtcsProjectStub } from '~~/stubs/create-ltcs-project-stub'
import { USER_ID_MIN } from '~~/stubs/create-user-stub'

const PROJECT_ID_MIN = USER_ID_MIN * 10

export function createLtcsProjectResponseStub (id: LtcsProjectId = PROJECT_ID_MIN): LtcsProjectsApi.GetResponse {
  const contract = createContractStub(id)
  return {
    ltcsProject: createLtcsProjectStub(id, contract)
  }
}
