/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { snakeToCamel } from '@zinger/helpers'
import { SnakeToCamel } from '@zinger/helpers/types'

type ConvertKeysToCamelCase<T> = {
  [K in keyof T as K extends string ? SnakeToCamel<K> : never]: T[K]
}

/**
 * オブジェクトのキーを snake_case から camelCase に変換する
 */
export const convertKeysToCamelCase = <T> (x: T): ConvertKeysToCamelCase<T> => {
  const entries = Object.entries(x).map(([key, value]) => [snakeToCamel(key), value])
  return Object.fromEntries(entries)
}
