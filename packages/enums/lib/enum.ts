/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert, keys } from '@zinger/helpers'

export type EnumerableDef = Record<string, string> | Record<string, number>

type ExhaustiveMatcher<T extends EnumerableDef, R> = {
  [K in keyof T]: () => R | never
}

type PartialMatcher<T extends EnumerableDef, R> = Partial<ExhaustiveMatcher<T, R>> & {
  default: () => R | never
}

type EnumerableMatcher<T extends EnumerableDef, R> = ExhaustiveMatcher<T, R> | PartialMatcher<T, R>

export type Enumerable<T extends EnumerableDef, U extends keyof T, V extends T[U]> = T & {
  readonly map: Readonly<Record<V, string>>
  readonly size: number
  readonly values: V[]
  keysMap (): Record<V, U>
  match<R> (x: V, matcher: EnumerableMatcher<T, R>): R
  resolve (x: V | string | number | undefined, alternative?: string): string
  validate (x: unknown): x is V
}

export const createEnumerable = <T extends EnumerableDef, U extends keyof T = keyof T, V extends T[U] = T[U]> (
  def: T,
  mapDef: [V, string][]
): Enumerable<T, U, V> => {
  const values = mapDef.map(([x, _]) => x)
  const defKeys = keys(def)
  const map = mapDef.reduce(
    (z, [value, label]) => ({ ...z, [value]: label }),
    {} as Readonly<Record<V, string>>
  )
  const validate = (x: unknown): x is V => values.includes(x as V)
  return {
    ...def,
    map,
    size: values.length,
    values,
    keysMap () {
      const entries = Object.entries(def).map(([k, v]) => [v, k] as const)
      return Object.fromEntries(entries) as any
    },
    match<R> (x: V, matcher: EnumerableMatcher<T, R>): R {
      return matcher[defKeys.find(key => def[key] === x) ?? 'default']!()
    },
    resolve (x: V | string | number | undefined, alternative: string = '-'): string {
      return validate(x) ? map[x] : alternative
    },
    validate
  }
}

function assertEnum<T extends EnumerableDef, U extends keyof T, V extends T[U]> (
  x: unknown,
  enumerable: Enumerable<T, U, V>
): asserts x is V {
  assert(enumerable.values.includes(x as V), `Invalid value: ${x}`)
}

export const parseEnum = <T extends EnumerableDef, U extends keyof T, V extends T[U]> (
  x: unknown,
  enumerable: Enumerable<T, U, V>
): V => {
  assertEnum<T, U, V>(x, enumerable)
  return x
}
