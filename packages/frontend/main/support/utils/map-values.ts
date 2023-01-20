/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export const mapValues = <T, R> (object: Record<string, T>, f: (x: T) => R): Record<string, R> => {
  const entries = Object.entries(object).map(([key, value]) => [key, f(value)])
  return Object.fromEntries(entries)
}
