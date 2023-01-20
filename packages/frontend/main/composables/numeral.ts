/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import defaultNumeral from 'numeral'

/**
 * 数値をformatに合わせて文字列に変換して返す.
 * value が無効な値の場合は '0' を返す.
 *
 * @param value 変換する数値
 * @param format 変換する形式（有効な値は Numeral.js に準拠する）デフォルト: 3桁区切り
 * @return string 変換後の文字列
 * @example
 *  numeral('10000') //  return '10,000'
 * @see http://numeraljs.com/
 */
export function numeral (value: number | string, format?: string): string {
  return defaultNumeral(value).format(format)
}

/**
 * 数値を 10000 で割ってから format に合わせて文字列に変換して返す.
 * value が無効な値の場合は '0' をフォーマットして返す.
 *
 * @param value 変換する数値
 * @param format 変換する形式（有効な値は Numeral.js に準拠する）デフォルト: 3桁区切り + 小数第二位まで
 * @return string 変換後の文字列
 * @example
 *  numeralWithDivision('100000') //  return '10.00'
 * @see http://numeraljs.com/
 */
export function numeralWithDivision (value: number | string, format = '0,0.00'): string {
  const v = (typeof value === 'string' ? +value : value) / 10000
  return numeral(v, format)
}
