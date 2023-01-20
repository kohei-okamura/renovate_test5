/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type Fn<T = any> = (...args: any[]) => T

type BaseMemoizeParams<T extends Fn> = {
  fn: T
  normalizer?: (...args: Parameters<T>) => string
}
type SyncMemoizeParams<T extends Fn> = BaseMemoizeParams<T> & {
  async?: false
}
type AsyncMemoizeParams<T extends Fn<Promise<any>>> = BaseMemoizeParams<T> & {
  async: true
}
type MemoizeParams<T extends Fn | Fn<Promise<any>>> = SyncMemoizeParams<T> | AsyncMemoizeParams<T>

type SyncMemo<T extends Fn> = Record<string, ReturnType<T>>
type AsyncMemo<T extends Fn<Promise<any>>> = Record<string, ReturnType<T> extends Promise<infer U> ? U : never>

type Memoized<T extends Fn> = (...args: Parameters<T>) => ReturnType<T>

type Memoize = {
  <T extends Fn> (f: T): Memoized<T>
  <T extends Fn> (params: SyncMemoizeParams<T>): Memoized<T>
  <T extends Fn<Promise<any>>> (params: AsyncMemoizeParams<T>): Memoized<T>
}

const defaultNormalizer = <T extends Fn> (...args: Parameters<T>): string => {
  return args.map(x => typeof x === 'string' ? x : JSON.stringify(x)).join('.')
}

const memoizeSync = <T extends Fn> (params: SyncMemoizeParams<T>) => {
  const fn = params.fn
  const memo: SyncMemo<T> = {}
  const exists = (key: string): boolean => key in memo
  const normalizer = params.normalizer ?? defaultNormalizer
  return (...args: Parameters<T>) => {
    const key = normalizer(...args)
    if (!exists(key)) {
      memo[key] = fn(...args)
    }
    console.log({ memo })
    return memo[key]
  }
}
const memoizeAsync = <T extends Fn<Promise<any>>> (params: AsyncMemoizeParams<T>) => {
  const fn = params.fn
  const memo: AsyncMemo<T> = {}
  const exists = (key: string): boolean => key in memo
  const normalizer = params.normalizer ?? defaultNormalizer
  return async (...args: Parameters<T>) => {
    const key = normalizer(...args)
    if (!exists(key)) {
      memo[key] = await fn(...args)
    }
    return memo[key]
  }
}
export const memoize: Memoize = <T extends Fn> (params: T | MemoizeParams<T>) => {
  if (typeof params === 'function') {
    return memoize<T>({ fn: params })
  } else if (params.async) {
    return memoizeAsync(params)
  } else {
    return memoizeSync(params)
  }
}
