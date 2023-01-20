/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createFaker } from '~~/stubs/fake'

export const ID_MAX = 100
export const ID_MIN = 1
export const STUB_COUNT = 1000
export const STUB_DEFAULT_SEED = 'zinger'

export const createSeeds = (n: number, seed: string = STUB_DEFAULT_SEED): string[] => {
  const faker = createFaker(seed)
  return Array.from(Array(n), () => faker.randomString(8))
}

export const SEEDS = createSeeds(STUB_COUNT * 3)

export type CreateStubs<T> = {
  (n?: number, skip?: number): T[]
}
