/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MimeType } from '@zinger/enums/lib/mime-type'
import { range } from '@zinger/helpers/index'
import { DwsBillingFile } from '~/models/dws-billing-file'
import { createFaker } from '~~/stubs/fake'
import { CreateStubs, SEEDS } from '~~/stubs/index'

const files = [
  '給付費明細',
  'サービス提供実績記録票',
  '上限額管理結果票'
]

export function createDwsBillingFileStub (id: number): DwsBillingFile {
  const faker = createFaker(SEEDS[id - 1])
  const mimeType = faker.randomElement(MimeType.values)
  return {
    name: `${files[faker.intBetween(0, files.length - 1)]}.${mimeType.split('/')[1]}`,
    token: faker.randomString(60),
    mimeType,
    createdAt: faker.randomDateString(),
    downloadedAt: faker.intBetween(1, 10) % 2 === 0 ? faker.randomDateString() : undefined
  }
}

export const createDwsBillingFileStubs: CreateStubs<DwsBillingFile> = (
  id: number = 1,
  n = 10,
  skip = 0
) => {
  const start = id + skip
  const end = start + n - 1
  return range(start, end).map(createDwsBillingFileStub)
}
