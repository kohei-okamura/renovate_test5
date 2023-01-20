/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * スネークケースをキャメルケースにして返す。
 *
 * @param snake snake_case の文字列
 * @return camelCase に変換した文字列
 */
export const snakeToCamel = (snake: string) => snake.replace(/_[a-z0-9]/gi, x => x.toUpperCase().replace('_', ''))
