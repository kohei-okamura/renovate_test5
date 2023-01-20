/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { TaxCategory } from '@zinger/enums/lib/tax-category'
import { TaxType } from '@zinger/enums/lib/tax-type'
import { range } from '@zinger/helpers'
import { OwnExpenseProgram, OwnExpenseProgramId } from '~/models/own-expense-program'
import { OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import ramenIpsum from '~~/stubs/fake/ramen-ipsum'
import { CreateStubs, ID_MAX, ID_MIN, SEEDS, STUB_COUNT } from '~~/stubs/index'

export const OWN_EXPENSE_PROGRAM_ID_MAX = ID_MAX
export const OWN_EXPENSE_PROGRAM_ID_MIN = ID_MIN
export const OWN_EXPENSE_PROGRAM_STUB_COUNT = STUB_COUNT

export function createOwnExpenseProgramStub (
  id: OwnExpenseProgramId = OWN_EXPENSE_PROGRAM_ID_MIN,
  taxType?: TaxType
): OwnExpenseProgram {
  const seed = SEEDS[id - 1]
  const faker = createFaker(seed)
  const ramen = ramenIpsum.factory(seed)
  const ownExpenseProgram: OwnExpenseProgram = {
    id,
    officeId: faker.randomElement([undefined, faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX)]),
    name: ramen.ipsum(10),
    durationMinutes: faker.intBetween(10, 360),
    fee: {
      taxExcluded: faker.intBetween(100, 5000),
      taxIncluded: faker.intBetween(100, 5000),
      taxType: taxType ?? faker.randomElement(TaxType.values),
      taxCategory: faker.randomElement(TaxCategory.values)
    },
    note: ramen.ipsum(10),
    isEnabled: faker.randomBoolean(),
    version: faker.intBetween(1, 3),
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
  return ownExpenseProgram
}

export const createOwnExpenseProgramStubs: CreateStubs<OwnExpenseProgram> = (
  n = OWN_EXPENSE_PROGRAM_STUB_COUNT, skip = 0
) => {
  const start = OWN_EXPENSE_PROGRAM_ID_MIN + skip
  const end = Math.min(start + n - 1, OWN_EXPENSE_PROGRAM_ID_MAX)
  const seed = SEEDS[n - 1]
  const faker = createFaker(seed)
  return range(start, end).map(i => {
    const taxType = faker.randomElement(TaxType.values)
    return createOwnExpenseProgramStub(i, taxType)
  })
}
