/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { range } from '@zinger/helpers/index'
import { DwsBilling } from '~/models/dws-billing'
import { LtcsBillingId } from '~/models/ltcs-billing'
import { createDwsBillingFileStubs } from '~~/stubs/create-dws-billing-file-stub'
import { createDwsBillingOfficeStub } from '~~/stubs/create-dws-billing-office-stub'
import { DWS_BILLING_ID_MIN, DWS_BILLING_STUB_COUNT } from '~~/stubs/create-dws-billing-stub-settings'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, SEEDS } from '~~/stubs/index'

type CreateStubParams = {
  id?: LtcsBillingId
}

export const createDwsBillingStub = ({ id = DWS_BILLING_ID_MIN }: CreateStubParams = {}): DwsBilling => {
  const faker = createFaker(SEEDS[id - 1])
  const status = faker.randomElement(DwsBillingStatus.values)
  return {
    id,
    office: createDwsBillingOfficeStub(faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)),
    transactedIn: faker.randomYearMonthString(),
    files: createDwsBillingFileStubs(),
    status,
    fixedAt: status === DwsBillingStatus.fixed ? faker.randomDateTimeString() : undefined,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createDwsBillingStubs: CreateStubs<DwsBilling> = (n = DWS_BILLING_STUB_COUNT, skip = 0) => {
  const start = DWS_BILLING_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(id => createDwsBillingStub({ id }))
}
