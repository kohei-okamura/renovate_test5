/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DwsBillingOffice } from '~/models/dws-billing-office'
import { OfficeId } from '~/models/office'
import { createOfficeStub, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import { SEEDS } from '~~/stubs/index'

export function createDwsBillingOfficeStub (officeId: OfficeId = OFFICE_ID_MIN): DwsBillingOffice {
  const { id, name, abbr, addr, tel } = createOfficeStub(officeId)
  const faker = createFaker(SEEDS[id - 1])
  return {
    officeId: id,
    code: faker.intBetween(1000000000, 9999999999).toString(),
    name,
    abbr,
    addr,
    tel
  }
}
