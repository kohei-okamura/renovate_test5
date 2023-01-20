/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { tap } from './tap'

/**
 * オブジェクトから指定したキーのみを抽出する.
 */
export const pick = <T, U extends keyof T> (target: T, keys: U[]): Pick<T, U> => keys.reduce(
  (z, key) => tap(z, () => {
    z[key] = target[key]
  }),
  {} as T
)
