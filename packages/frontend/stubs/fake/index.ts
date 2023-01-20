/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Sex } from '@zinger/enums/lib/sex'
import { DateTime } from 'luxon'
import * as RandomSeed from 'random-seed'
import { Addr } from '~/models/addr'
import { ISO_DATETIME_FORMAT, ISO_MONTH_FORMAT } from '~/models/date'
import { Location } from '~/models/location'
import { TimeRange } from '~/models/range'
import { StructuredName } from '~/models/structured-name'
import { addrs } from '~~/stubs/fake/addrs'
import { domains } from '~~/stubs/fake/domains'
import { familyNames, givenNames } from '~~/stubs/fake/names'
import { phoneNumbers } from '~~/stubs/fake/phone-numbers'

const alphabets = 'abcdefghijklmnopqrstuvwxyz'
const numbers = '0123456789'
const dateRange = {
  // max = 2025-05-17 00:00:00
  max: 1747407600,
  // min = 1970-05-17 00:00:00
  min: 11718000
}

type RandomDateStringArguments = {
  range?: { min: number, max: number }
  yearMonth?: string
}

export interface Fake {
  addr: Addr
  email: string
  fax: string
  location: Location
  name: StructuredName
  sex: Sex
  tel: string
}

export interface Faker {
  createFake (): Fake
  floatBetween (min: number, max: number): number
  intBetween (min: number, max: number): number
  randomAlphabets (n: number): string
  randomArray<T> (xs: T[], n: number): T[]
  randomBoolean (): boolean
  randomDate (min?: number, max?: number): DateTime
  randomDateTime (start?: number, end?: number): DateTime
  randomDateString (args?: RandomDateStringArguments): string
  randomDateTimeString (args?: RandomDateStringArguments): string
  randomYearMonthString (): string
  randomElement<T> (xs: T[]): T
  randomElement<T, R> (xs: T[], f: (x: T) => R): R
  randomElements<T> (xs: T[], n: number): T[]
  randomNumericString (n: number): string
  randomString (n: number, table?: string): string
  randomTimeString (): string
  randomTimeRange (): TimeRange
}

class FakerImpl implements Faker {
  private readonly random: RandomSeed.RandomSeed

  constructor (seed: string) {
    this.random = RandomSeed.create(seed)
  }

  createFake (): Fake {
    const sex = this.randomElement([Sex.male, Sex.female])
    const familyName = this.randomElement(familyNames)
    const givenName = this.randomElement(givenNames[sex])
    const phone = this.randomElement(phoneNumbers)
    return {
      addr: this.randomElement(addrs),
      email: this.randomEmailAddress(),
      fax: phone.fax,
      // TODO: 緯度経度をランダムに生成する
      location: {
        lat: 0.0,
        lng: 0.0
      },
      name: {
        familyName: familyName.kanji,
        givenName: givenName.kanji,
        displayName: `${familyName.kanji} ${givenName.kanji}`,
        phoneticFamilyName: familyName.kana,
        phoneticGivenName: givenName.kana,
        phoneticDisplayName: `${familyName.kana} ${givenName.kana}`
      },
      sex,
      tel: phone.tel
    }
  }

  floatBetween (min: number, max: number): number {
    return this.random.floatBetween(min, max)
  }

  intBetween (min: number, max: number): number {
    return this.random.intBetween(min, max)
  }

  randomAlphabets (n: number): string {
    return this.randomString(n, alphabets)
  }

  randomArray<T> (xs: T[], n: number): T[] {
    const result: T[] = []
    for (let i = n; i > 0; --i) {
      result.push(this.randomElement(xs.filter(x => !result.includes(x))))
    }
    return xs.filter(x => result.includes(x))
  }

  randomBoolean (): boolean {
    return this.randomElement([true, false])
  }

  randomDate (start?: number, end?: number): DateTime {
    return this.randomDateTime(start, end).startOf('day')
  }

  randomDateString (args: RandomDateStringArguments = {}): string {
    const [min, max] = (({ range, yearMonth }) => {
      if (range) {
        return [range.min, range.max]
      } else if (yearMonth) {
        const d = DateTime.fromISO(yearMonth)
        return [
          d.startOf('month').toSeconds(),
          d.endOf('month').toSeconds()
        ]
      } else {
        return [undefined, undefined]
      }
    })(args)
    return this.randomDate(min, max).toISODate()
  }

  randomDateTime (start = dateRange.min, end = dateRange.max): DateTime {
    const x = this.intBetween(start, end)
    return DateTime.fromSeconds(x).setLocale('ja').setZone('Asia/Tokyo')
  }

  randomDateTimeString (args: RandomDateStringArguments = {}): string {
    const [min, max] = (({ range, yearMonth }) => {
      if (range) {
        return [range.min, range.max]
      } else if (yearMonth) {
        const d = DateTime.fromISO(yearMonth)
        return [
          d.startOf('month').toSeconds(),
          d.endOf('month').toSeconds()
        ]
      } else {
        return [undefined, undefined]
      }
    })(args)
    return this.randomDateTime(min, max).toFormat(ISO_DATETIME_FORMAT)
  }

  randomYearMonthString (): string {
    return this.randomDateTime().toFormat(ISO_MONTH_FORMAT)
  }

  randomElement<T, R = never> (xs: T[], f?: (x: T) => R): T | R {
    const x = xs[this.intBetween(0, xs.length - 1)]
    return f ? f(x) : x
  }

  randomElements<T> (xs: T[], n: number): T[] {
    if (n === 0) {
      return []
    }
    const copy = xs.slice()
    return n === copy.length ? copy : [...Array(n)].flatMap(() => copy.splice(this.intBetween(1, copy.length) - 1, 1))
  }

  randomEmailAddress (): string {
    const domain = this.randomElement(domains)
    const subDomain = this.randomString(3, alphabets)
    const name = this.randomString(8, alphabets)
    const suffix = this.randomString(4, numbers)
    return `${name}${suffix}@${subDomain}.${domain}`
  }

  randomNumericString (n: number): string {
    return this.randomString(n, numbers)
  }

  randomString (n: number, table?: string): string {
    const t = table ?? alphabets + numbers
    let x = ''
    for (let i = n; i > 0; --i) {
      x += t.charAt(this.random(t.length))
    }
    return x
  }

  randomTimeString (): string {
    return `${this.intBetween(0, 23).toString().padStart(2, '0')}:${this.randomElement(['00', '30'])}`
  }

  randomTimeRange (): TimeRange {
    const time = [this.randomTimeString(), this.randomTimeString()]
    time.sort()
    return { start: time[0], end: time[1] }
  }
}

export function createFaker (seed: string): Faker {
  return new FakerImpl(seed)
}
