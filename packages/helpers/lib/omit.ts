/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from './keys'
import { tap } from './tap'

/**
 * オブジェクトから指定したキーを除外する.
 */
export const omit = <T extends Record<string, any>, U extends keyof T> (target: T, omitKeys: U[]): Omit<T, U> => {
  return keys(target).filter(key => omitKeys.every(x => x !== key)).reduce(
    (z, key) => tap(z, () => {
      z[key] = target[key]
    }),
    {} as T
  )
}
