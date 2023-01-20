/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { Job } from '~/models/job'
import { createFaker } from '~~/stubs/fake'
import { STUB_DEFAULT_SEED } from '~~/stubs/index'

export function createJobStub (token: Job['token'], status: JobStatus): Job {
  const seed = `${STUB_DEFAULT_SEED}:${token}:${status}`
  const faker = createFaker(seed)
  return {
    token,
    data: {
      filename: 'example.xlsx',
      uri: '/example.xlsx'
    },
    status,
    createdAt: faker.randomDateString(),
    updatedAt: faker.randomDateString()
  }
}
