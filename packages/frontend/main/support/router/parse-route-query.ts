/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { isNumeric } from '~/support/jaco'
import { RouteQuery } from '~/support/router/types'

type Parsable = boolean | number | string | undefined | Parsable[]

type ParamsDef = Record<string, Parsable>

type BaseParamOption<T, U> = {
  type: T
  default?: U
}
type ArrayParamOption = BaseParamOption<typeof Array, Parsable[]> & {
  map?: (x: any) => any
}
type BooleanParamOption = BaseParamOption<typeof Boolean, boolean | '' | undefined>
type NumberParamOption = BaseParamOption<typeof Number, number | '' | undefined>
type StringParamOption = BaseParamOption<typeof String, string | undefined>
type ParamOption<T extends Parsable> = T extends any[]
  ? ArrayParamOption
  : T extends boolean
    ? BooleanParamOption
    : T extends number
      ? NumberParamOption
      : T extends string
        ? StringParamOption
        : never

export type ParamOptions<T extends ParamsDef> = {
  readonly [K in keyof T]: ParamOption<T[K]>
}

function isArrayParamOptions (options: BaseParamOption<any, any>): options is ArrayParamOption {
  return options.type === Array
}

function isBooleanParamOptions (options: BaseParamOption<any, any>): options is BooleanParamOption {
  return options.type === Boolean
}

function isNumberParamOptions (options: BaseParamOption<any, any>): options is NumberParamOption {
  return options.type === Number
}

const parse = <T extends Parsable> (x: any | undefined, options: ParamOption<T>): Parsable => {
  if (isArrayParamOptions(options)) {
    if (x === undefined) {
      return options.default
    }
    const xs = Array.isArray(x) ? x : [x]
    return options.map
      ? xs.map(options.map)
      : xs
  } else if (isBooleanParamOptions(options)) {
    switch (x) {
      case undefined:
        return options.default
      case 'true':
        return true
      case 'false':
        return false
      default:
        return undefined
    }
  } else if (isNumberParamOptions(options)) {
    return x === undefined || !isNumeric(x) ? options.default : +x
  } else {
    return x ?? options.default
  }
}

export const parseRouteQuery = <T extends ParamsDef> (routeQuery: RouteQuery, options: ParamOptions<T>): Partial<T> => {
  const xs = keys(options).map(key => [key, parse(routeQuery[key], options[key])])
  return Object.fromEntries(xs)
}
