/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { option, Option } from 'ts-option'
import { Time } from '~/models/time'

const MINUTES_IN_DAY = 24 * 60
const PATTERN = /(\d+):([0-5][0-9])/

type BaseTimeDuration<T> = {
  /**
   * 値がゼロかどうかを判定する.
   */
  readonly isZero: boolean

  /**
   * 指定された {@link TimeDuration} と等価かどうかを判定する.
   */
  // eslint-disable-next-line no-use-before-define
  equalsTo (that: TimeDuration): boolean

  /**
   * 指定された {@link TimeDuration} より小さい or 等価かどうかを判定する.
   */
  // eslint-disable-next-line no-use-before-define
  lessThanOrEqualTo (that: TimeDuration): boolean

  /**
   * 指定された {@link TimeDuration} との差を返す.
   */
  // eslint-disable-next-line no-use-before-define
  minus (that: TimeDuration): TimeDuration

  /**
   * 指定された {@link TimeDuration} との合計を返す.
   */
  // eslint-disable-next-line no-use-before-define
  plus (that: TimeDuration): TimeDuration

  /**
   * プリミティブ値を返す.
   */
  valueOf (): T

  /**
   * 「時」成分を変更したインスタンスを取得する.
   */
  // eslint-disable-next-line no-use-before-define
  withHours (hours: number | string | undefined): TimeDuration

  /**
   * 「分」成分を変更したインスタンスを取得する.
   */
  // eslint-disable-next-line no-use-before-define
  withMinutes (minutes: number | string | undefined): TimeDuration
}

/**
 * 有効な TimeDuration.
 */
class ValidTimeDurationImpl implements BaseTimeDuration<number> {
  readonly isValid = true
  readonly totalMinutes: number

  public constructor (readonly hours: number, readonly minutes: number) {
    this.totalMinutes = hours * 60 + minutes
  }

  get isZero (): boolean {
    return this.totalMinutes === 0
  }

  // eslint-disable-next-line no-use-before-define
  equalsTo (that: TimeDuration): boolean {
    return that.isValid && this.totalMinutes === that.totalMinutes
  }

  // eslint-disable-next-line no-use-before-define
  lessThanOrEqualTo (that: TimeDuration): boolean {
    return that.isValid && this.totalMinutes <= that.totalMinutes
  }

  // eslint-disable-next-line no-use-before-define
  minus (that: TimeDuration): TimeDuration {
    return that.isValid ? fromMinutes(this.totalMinutes - that.totalMinutes) : this
  }

  // eslint-disable-next-line no-use-before-define
  plus (that: TimeDuration): TimeDuration {
    return that.isValid ? fromMinutes(this.totalMinutes + that.totalMinutes) : this
  }

  valueOf (): number {
    return this.totalMinutes
  }

  // eslint-disable-next-line no-use-before-define
  withHours (hours: number | string | undefined): TimeDuration {
    return create(hours, this.minutes)
  }

  // eslint-disable-next-line no-use-before-define
  withMinutes (minutes: number | string | undefined): TimeDuration {
    return create(this.hours, minutes)
  }
}

/**
 * 無効な TimeDuration.
 */
class InvalidTimeDurationImpl implements BaseTimeDuration<undefined> {
  readonly isValid = false
  readonly isZero = false
  readonly totalMinutes = undefined

  public constructor (readonly hours: number | string | undefined, readonly minutes: number | string | undefined) {
    // Nothing to do.
  }

  // eslint-disable-next-line no-use-before-define
  equalsTo (that: TimeDuration): boolean {
    return this.hours === that.hours && this.minutes === that.minutes
  }

  // eslint-disable-next-line no-use-before-define
  lessThanOrEqualTo (): boolean {
    return false
  }

  // eslint-disable-next-line no-use-before-define
  minus (that: TimeDuration): TimeDuration {
    return that.isValid ? that : this
  }

  // eslint-disable-next-line no-use-before-define
  plus (that: TimeDuration): TimeDuration {
    return that.isValid ? that : this
  }

  valueOf (): undefined {
    return undefined
  }

  // eslint-disable-next-line no-use-before-define
  withHours (hours: number | string | undefined): TimeDuration {
    return create(hours, this.minutes)
  }

  // eslint-disable-next-line no-use-before-define
  withMinutes (minutes: number | string | undefined): TimeDuration {
    return create(this.hours, minutes)
  }
}

export type ValidTimeDuration = ValidTimeDurationImpl
export type InvalidTimeDuration = InvalidTimeDurationImpl
export type TimeDuration = ValidTimeDuration | InvalidTimeDuration

/**
 * パラメータを指定して {@link TimeDuration} のインスタンスを生成する.
 */
const create = (hours: number | string | undefined, minutes: number | string | undefined): TimeDuration => {
  return typeof hours === 'number' && typeof minutes === 'number'
    ? new ValidTimeDurationImpl(hours, minutes)
    : new InvalidTimeDurationImpl(hours, minutes)
}

/**
 * 文字列から {@link TimeDuration} のインスタンスを生成する.
 */
const parse = (input: string | undefined): Option<ValidTimeDuration> => option(input)
  .flatMap(x => option(x.match(PATTERN)))
  .map(m => new ValidTimeDurationImpl(+m[1], +m[2]))

/**
 * 合計時間量（分単位）から {@link TimeDuration} のインスタンスを生成する.
 */
const fromMinutes = (n: number): ValidTimeDuration => new ValidTimeDurationImpl(Math.floor(n / 60), n % 60)

/**
 * 文字列、整数、または {@link TimeDuration} のインスタンスから {@link TimeDuration} のインスタンスを生成する.
 */
const from = (input: string | number | TimeDuration | undefined): Option<TimeDuration> => {
  if (typeof input === 'number') {
    return option(fromMinutes(input))
  } else if (typeof input === 'string') {
    return parse(input)
  } else {
    return option(input)
  }
}

/**
 * 与えられた値が {link TimeDuration} かどうかを判定する.
 */
function isTimeDuration (input: unknown): input is TimeDuration {
  return input instanceof ValidTimeDurationImpl || input instanceof InvalidTimeDurationImpl
}

let zeroValue: ValidTimeDuration | undefined

/**
 * ゼロ時間ゼロ分の {@link TimeDuration} インスタンスを返す.
 */
const zero = (): ValidTimeDuration => {
  if (typeof zeroValue === 'undefined') {
    zeroValue = fromMinutes(0)
  }
  return zeroValue
}

/**
 * 2つの時刻の間の経過時間を返す.
 *
 * `to` が `from` よりも前の時刻だった場合は翌日であるとして計算する.
 */
const diff = (from: Time | undefined, to: Time | undefined): Option<ValidTimeDuration> => {
  return parse(from).flatMap(x => parse(to).map(y => fromMinutes(
    x < y ? y.totalMinutes - x.totalMinutes : MINUTES_IN_DAY - x.totalMinutes + y.totalMinutes
  )))
}

// eslint-disable-next-line @typescript-eslint/no-redeclare -- intentionally naming the variable the same as the type
export const TimeDuration = {
  create,
  diff,
  from,
  fromMinutes,
  isTimeDuration,
  parse,
  zero
}
