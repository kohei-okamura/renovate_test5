/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { eraDate, eraMonth } from '~/composables/era-date'

describe('composables/era-date', () => {
  describe('eraDate', () => {
    it.each([
      '',
      'not a date',
      '2020-1-01',
      '2020-01-1'
    ])('should be return "-" when argument is %s ', input => {
      expect(eraDate(input)).toEqual('-')
    })

    it.each([
      ['end', 'Meiji', '1912-07-29', '明45.07.29', '明治45年7月29日'],
      ['beginning', 'Taisho', '1912-07-30', '大01.07.30', '大正1年7月30日'],
      ['end', 'Taisho', '1926-12-24', '大15.12.24', '大正15年12月24日'],
      ['beginning', 'Showa', '1926-12-25', '昭01.12.25', '昭和1年12月25日'],
      ['end', 'Showa', '1989-01-07', '昭64.01.07', '昭和64年1月7日'],
      ['beginning', 'Heisei', '1989-01-08', '平01.01.08', '平成1年1月8日'],
      ['end', 'Heisei', '2019-04-30', '平31.04.30', '平成31年4月30日'],
      ['beginning', 'Reiwa', '2019-05-01', '令01.05.01', '令和1年5月1日']
    ] as const)(
      'should be return correct era date when passed the %s of the %s era date string',
      (_, _era, input, short, long) => {
        expect(eraDate(input, 'short')).toEqual(short)
        expect(eraDate(input, 'long')).toEqual(long)
        expect(eraDate(input)).toEqual(long)
      }
    )
  })

  describe('eraMonth', () => {
    it.each([
      '',
      'not a date',
      '2020-1-01',
      '2020-01-1'
    ])('should be return "-" when argument is %s ', input => {
      expect(eraMonth(input)).toEqual('-')
    })

    it.each([
      ['end', 'Meiji', '1912-07-29', '明治45年7月'],
      ['beginning', 'Taisho', '1912-07-30', '大正1年7月'],
      ['end', 'Taisho', '1926-12-24', '大正15年12月'],
      ['beginning', 'Showa', '1926-12-25', '昭和1年12月'],
      ['end', 'Showa', '1989-01-07', '昭和64年1月'],
      ['beginning', 'Heisei', '1989-01-08', '平成1年1月'],
      ['end', 'Heisei', '2019-04-30', '平成31年4月'],
      ['beginning', 'Reiwa', '2019-05-01', '令和1年5月']
    ])('should be return correct era month when passed the %s of the %s era date string', (_, _era, input, expected) => {
      expect(eraMonth(input)).toEqual(expected)
    })

    it.each([
      ['Taisho', '1912-07', '大正1年7月'],
      ['Showa', '1926-12', '昭和1年12月'],
      ['Heisei', '1989-01', '平成1年1月'],
      ['Reiwa', '2019-05', '令和1年5月']
    ])('should be return correct era month when passed the month string that changed to %s', (_era, input, expected) => {
      expect(eraMonth(input)).toEqual(expected)
    })
  })
})
