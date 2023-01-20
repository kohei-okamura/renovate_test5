/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContractStatus } from '@zinger/enums/lib/contract-status'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { range } from '@zinger/helpers'
import { Contract, ContractId } from '~/models/contract'
import { UserId } from '~/models/user'
import { SEEDS, STUB_DEFAULT_SEED } from '~~/stubs'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { USER_ID_MAX, USER_ID_MIN } from '~~/stubs/create-user-stub'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'

export const CONTRACT_ID_MAX = USER_ID_MAX * 10 + 9
export const CONTRACT_ID_MIN = USER_ID_MIN * 10

export function createContractStub (id: ContractId = CONTRACT_ID_MIN): Contract {
  const userId = Math.floor(id / 10)
  const seed = STUB_DEFAULT_SEED + `${userId}`.padStart(4, '0') + `${id}`.padStart(4, '0')
  const faker = createFaker(seed)
  const ramen = ramenIpsum.factory(seed)
  const serviceSegment = faker.randomElement([
    ServiceSegment.disabilitiesWelfare,
    ServiceSegment.longTermCare
  ])
  const status = faker.randomElement(ContractStatus.values)
  const contractedOn = status !== ContractStatus.provisional
    ? faker.randomDate()
    : undefined
  const terminatedOnDays = faker.intBetween(100, 2000)
  const terminatedOn = status === ContractStatus.terminated
    ? contractedOn?.plus({ days: terminatedOnDays })
    : undefined
  const expiredReason = !(serviceSegment !== ServiceSegment.longTermCare || status !== ContractStatus.terminated)
    ? faker.randomElement(LtcsExpiredReason.values)
    : LtcsExpiredReason.unspecified
  const createPeriod = (segment: ServiceSegment) => {
    if (serviceSegment === segment && status !== ContractStatus.provisional) {
      const start = contractedOn?.plus({ days: faker.intBetween(1, 99) })
      const end = start?.plus({ days: faker.intBetween(100, terminatedOnDays) })
      return { start: start?.toISODate(), end: end?.toISODate() }
    } else {
      return { start: undefined, end: undefined }
    }
  }
  const dwsPeriods = {
    [DwsServiceDivisionCode.homeHelpService]: createPeriod(ServiceSegment.disabilitiesWelfare),
    [DwsServiceDivisionCode.visitingCareForPwsd]: createPeriod(ServiceSegment.disabilitiesWelfare)
  }
  const ltcsPeriod = createPeriod(ServiceSegment.longTermCare)
  return {
    id,
    userId,
    officeId: faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX),
    serviceSegment,
    status,
    contractedOn: contractedOn?.toISODate(),
    terminatedOn: terminatedOn?.toISODate(),
    dwsPeriods,
    ltcsPeriod,
    expiredReason,
    note: ramen.ipsum(255),
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export function createContractStubsForUser (userId: UserId): Contract[] {
  const count = userId === USER_ID_MIN ? 5 : createFaker(SEEDS[userId - 1]).intBetween(0, 2)
  return range(0, count).map(i => createContractStub(userId * 10 + i))
}
