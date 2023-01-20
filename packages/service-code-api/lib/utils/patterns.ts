/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { alt, isParser, Parser, regexp, seqObj, string } from 'parsimmon'

export const dot = string('・')

export const empty = string('')

export const hours = regexp(/\d+\.\d+/).map(Number)

export const minutes = alt(
  hours.map(x => x * 60),
  string('').map(_ => 0)
)

export const option = (symbol: string) => alt(
  string(symbol).map(_ => true),
  empty.map(_ => false)
)

export const dotAndOption = (symbol: string) => alt(
  dot.then(string(symbol)).map(_ => true),
  empty.map(_ => false)
)

export const patternMatch = <T> (...patterns: [Parser<string>, T][]) => alt(
  ...patterns.map(([pattern, value]) => pattern.map(_ => value))
)

type Parsers<T, U extends keyof T = keyof T> = {
  [K in U]: Parser<T[K]>
}

type SeqObjectEntry<T, U extends keyof T> = Partial<Parsers<T, U>> | Parser<any>

export const seqObject = <T, U extends keyof T = keyof T> (...xs: SeqObjectEntry<T, U>[]): Parser<Pick<T, U>> => {
  const parsers = xs.flatMap<[U, Parser<T[U]>] | Parser<any>>(x => {
    return isParser(x) ? [x] : keys(x).map<[U, Parser<T[U]>]>(key => [key, x[key]!])
  })
  return seqObj<T, U>(...parsers)
}
