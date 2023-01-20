/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { BillingDestination } from '@zinger/enums/lib/billing-destination'
import { ContactRelationship } from '@zinger/enums/lib/contact-relationship'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { range } from '@zinger/helpers'
import { User, UserId } from '~/models/user'
import { BANK_ID_MAX, BANK_ID_MIN } from '~~/stubs/create-bank-account-stub'
import { createOfficeStub, OFFICE_ID_MAX, OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, ID_MAX, ID_MIN, SEEDS } from '~~/stubs/index'

export const USER_ID_MAX = ID_MAX
export const USER_ID_MIN = ID_MIN
export const USER_STUB_COUNT = USER_ID_MAX - USER_ID_MIN + 1

export function createUserStub (id: UserId = USER_ID_MIN): User {
  const faker = createFaker(SEEDS[id - 1])
  const fake = faker.createFake()
  const { name } = createOfficeStub(faker.intBetween(OFFICE_ID_MIN, OFFICE_ID_MAX))
  return {
    id,
    name: fake.name,
    sex: fake.sex,
    birthday: faker.randomDateString(),
    addr: fake.addr,
    // TODO: 緯度・経度をランダムに設定する.
    location: {
      lat: 0.0,
      lng: 0.0
    },
    contacts: range(1, faker.intBetween(1, 3)).map(() => {
      const relationship = faker.randomElement(ContactRelationship.values)
      const contactFake = faker.createFake()
      return {
        tel: contactFake.tel,
        relationship,
        name: relationship !== ContactRelationship.theirself ? contactFake.name.displayName : ''
      }
    }),
    bankAccountId: faker.intBetween(BANK_ID_MIN, BANK_ID_MAX),
    billingDestination: {
      destination: faker.randomElement(BillingDestination.values.filter(x => x !== BillingDestination.none)),
      paymentMethod: faker.randomElement(PaymentMethod.values.filter(x => x !== PaymentMethod.none)),
      contractNumber: faker.randomNumericString(10),
      corporationName: name,
      agentName: fake.name.displayName,
      addr: fake.addr,
      tel: fake.tel
    },
    isEnabled: faker.randomBoolean(),
    version: 1,
    createdAt: faker.randomDateTimeString(),
    updatedAt: faker.randomDateTimeString()
  }
}

export const createUserStubs: CreateStubs<User> = (n = USER_STUB_COUNT, skip = 0) => {
  const start = USER_ID_MIN + skip
  const end = start + n - 1
  return range(start, end).map(createUserStub)
}
