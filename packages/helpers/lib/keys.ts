/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type KeyOf<T> = keyof T & string

/**
 * `Object.keys` よりちょっぴり型安全に `Object` のキーの一覧を取得する.
 */
export const keys = <T extends Record<string, unknown>> (x: T) => Object.keys(x) as KeyOf<T>[]
