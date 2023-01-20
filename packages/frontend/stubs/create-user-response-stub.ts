/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { UserId } from '~/models/user'
import { UsersApi } from '~/services/api/users-api'
import { createBankAccountStub } from '~~/stubs/create-bank-account-stub'
import { createContractStubsForUser } from '~~/stubs/create-contract-stub'
import { createDwsCertificationStubsForUser } from '~~/stubs/create-dws-certification-stub'
import { createDwsProjectStubs } from '~~/stubs/create-dws-project-stub'
import { createDwsSubsidiesStub } from '~~/stubs/create-dws-subsidy-stub'
import { createLtcsInsCardStubsForUser } from '~~/stubs/create-ltcs-ins-card-stub'
import { createLtcsProjectStubs } from '~~/stubs/create-ltcs-project-stub'
import { createLtcsSubsidyStubsForUser } from '~~/stubs/create-ltcs-subsidy-stub'
import { createUserDwsCalcSpecStubsForUser } from '~~/stubs/create-user-dws-calc-spec-stub'
import { createUserLtcsCalcSpecStubsForUser } from '~~/stubs/create-user-ltcs-calc-spec-stub'
import { createUserStub } from '~~/stubs/create-user-stub'

export function createUserResponseStub (id: UserId = 1): UsersApi.GetResponse {
  const user = createUserStub(id)
  const contracts = createContractStubsForUser(id)
  return {
    bankAccount: createBankAccountStub(user.bankAccountId),
    contracts,
    dwsCertifications: createDwsCertificationStubsForUser(user.id),
    dwsProjects: createDwsProjectStubs(contracts),
    dwsSubsidies: createDwsSubsidiesStub(user.id),
    dwsCalcSpecs: createUserDwsCalcSpecStubsForUser(user.id),
    ltcsInsCards: createLtcsInsCardStubsForUser(user.id),
    ltcsProjects: createLtcsProjectStubs(contracts),
    ltcsSubsidies: createLtcsSubsidyStubsForUser(user.id),
    ltcsCalcSpecs: createUserLtcsCalcSpecStubsForUser(user.id),
    user
  }
}
