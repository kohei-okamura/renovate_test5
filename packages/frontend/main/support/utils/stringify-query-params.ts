/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import qs from 'qs'
import { mapValues } from '~/support/utils/map-values'

type QueryParams = {
  [K: string]: Primitive | Primitive[] | QueryParams | QueryParams[]
}

/**
 * 空文字を取り除く.
 *
 * qs のライブラリにおいて、以下のプルリクで同様の機能が実装予定のため、実装されたら置き換える.
 * @link https://github.com/ljharb/qs/pull/226
 */
type RemoveEmptyStringRecursive = {
  (x: '' | null): undefined
  <T> (x: T): T
  <T> (x: T[]): T[]
}
const removeEmptyStringRecursive: RemoveEmptyStringRecursive = (x: any) => {
  if (x === null) {
    return undefined
  } else if (Array.isArray(x)) {
    return x.map(removeEmptyStringRecursive)
  } else if (typeof x === 'object') {
    return mapValues(x, removeEmptyStringRecursive)
  } else {
    return x === '' ? undefined : x
  }
}

/**
 * オブジェクトをクエリパラメーター文字列に変換する.
 */
export const stringifyQueryParams = (params: QueryParams): string => {
  return qs.stringify(removeEmptyStringRecursive(params), {
    arrayFormat: 'brackets',
    skipNulls: true
  })
}
