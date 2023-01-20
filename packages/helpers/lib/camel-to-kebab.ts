/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * キャメルケースをケバブケースにして返す。
 * Camel to Kebab は下記のリンク先から拝借した。
 *
 * @param camel キャメルケースの文字列
 * @return ケバブケースに変換した文字列
 * @see https://gist.github.com/nblackburn/875e6ff75bc8ce171c758bf75f304707
 */
export function camelToKebab (camel: string) {
  return camel.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2').toLowerCase()
}
