/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * キャメルケースをスネークケースにして返す。
 *
 * @param camel camelCase の文字列
 * @return snake_case に変換した文字列
 * @see https://gist.github.com/nblackburn/875e6ff75bc8ce171c758bf75f304707
 */
export const camelToSnake = (camel: string) => camel.replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1_$2').toLowerCase()
