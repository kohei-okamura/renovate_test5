/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 指定した範囲の数値を要素とする配列を生成する.
 */
export const range = (min: number, max: number): number[] => Array.from(Array(max - min + 1), (_, index) => index + min)
