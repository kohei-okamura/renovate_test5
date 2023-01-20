/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MimeType } from '@zinger/enums/lib/mime-type'
import { range } from '@zinger/helpers/index'
import { LtcsBillingFile } from '~/models/ltcs-billing-file'
import { LTCS_BILLING_SEEDS } from '~~/stubs/create-ltcs-billing-stub-settings'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs } from '~~/stubs/index'

const files = [
  '給付費明細'
]

export const createLtcsBillingFileStub = (id: number): LtcsBillingFile => {
  const faker = createFaker(LTCS_BILLING_SEEDS[id - 1])
  const mimeType = faker.randomElement(MimeType.values)
  return {
    name: `${files[faker.intBetween(0, files.length - 1)]}.${mimeType.split('/')[1]}`,
    token: faker.randomString(60),
    mimeType,
    createdAt: faker.randomDateTimeString(),
    downloadedAt: faker.intBetween(1, 10) % 2 === 0 ? faker.randomDateTimeString() : undefined
  }
}

export const createLtcsBillingFileStubs: CreateStubs<LtcsBillingFile> = (id: number = 1, n = 10, skip = 0) => {
  const start = id + skip
  const end = start + n - 1
  return range(start, end).map(createLtcsBillingFileStub)
}
