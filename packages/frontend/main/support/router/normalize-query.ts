/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { Route } from 'vue-router'

type RouteQuery = Route['query']

const normalizePrimitive = (x: Primitive): string | undefined => {
  const t = typeof x
  if (t === 'boolean') {
    return x ? 'true' : 'false'
  } else if (t === 'number' || t === 'string') {
    return `${x}`
  } else {
    return undefined
  }
}

const normalizeArray = (xs: Primitive[]): string[] => xs.flatMap(x => {
  const z = normalizePrimitive(x)
  return z === undefined ? [] : [z]
})

const normalize = (x: Primitive | Primitive[]): string | string[] | undefined => {
  return Array.isArray(x) ? normalizeArray(x) : normalizePrimitive(x)
}

export const normalizeQuery = (query: Record<string, Primitive | Primitive[]>): RouteQuery => {
  const xs = keys(query)
    .map(key => [key, normalize(query[key])])
    .filter(x => x[1] !== undefined)
  return Object.fromEntries(xs)
}
