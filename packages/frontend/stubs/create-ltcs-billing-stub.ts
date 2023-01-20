/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { range } from '@zinger/helpers/index'
import { LtcsBilling, LtcsBillingId } from '~/models/ltcs-billing'
import { CreateStubs } from '~~/stubs'
import { createLtcsBillingFileStubs } from '~~/stubs/create-ltcs-billing-file-stub'
import { createLtcsBillingOfficeStub } from '~~/stubs/create-ltcs-billing-office-stub'
import {
  LTCS_BILLING_ID_MIN,
  LTCS_BILLING_SEEDS,
  LTCS_BILLING_STUB_COUNT
} from '~~/stubs/create-ltcs-billing-stub-settings'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'

type CreateStubParams = {
  id?: LtcsBillingId
}

export const createLtcsBillingStub = ({ id = LTCS_BILLING_ID_MIN }: CreateStubParams = {}): LtcsBilling => {
  const faker = createFaker(LTCS_BILLING_SEEDS[id - 1])
  const status = faker.randomElement(LtcsBillingStatus.values)
  return {
    id,
    office: createLtcsBillingOfficeStub(faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)),
    transactedIn: faker.randomYearMonthString(),
    files: createLtcsBillingFileStubs(),
    status,
    fixedAt: status === LtcsBillingStatus.fixed ? faker.randomDateTimeString() : undefined,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createLtcsBillingStubs: CreateStubs<LtcsBilling> = (n = LTCS_BILLING_STUB_COUNT, skip = 0) => {
  const start = LTCS_BILLING_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(id => createLtcsBillingStub({ id }))
}
