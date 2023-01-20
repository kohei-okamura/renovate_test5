/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 重複を取り除いた要素の配列を返す.
 */
export const distinct = <T> (...xs: T[]): T[] => Array.from(new Set(xs))
