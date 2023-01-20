/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * ちょっとだけ型安全な `Object.assign`.
 */
export const assign = <T> (target: T, source: Partial<T>): T => Object.assign(target, source)
