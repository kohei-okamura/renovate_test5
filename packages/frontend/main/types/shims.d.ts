/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare type Dictionary<T = any> = Record<string, T>

declare type EventType = keyof HTMLElementEventMap

declare type Primitive = boolean | number | string | null | undefined

declare type DeepPartial<T> = {
  [K in keyof T]?: T[K] extends Primitive | Date | File
    ? T[K]
    : (T[K] extends Array<infer U> ? DeepPartial<U>[] : DeepPartial<T[K]>)
}

declare type Writable<T> = {
  -readonly [K in keyof T]?: T[K] extends Primitive | Date | File
    ? T[K]
    : (T[K] extends Array<infer U> ? Writable<U>[] : Writable<T[K]>)
}

declare type Lazy<T> = () => T

declare type ObjectPath<T> = {
  [K in keyof T]: K extends string ? (T[K] extends Record<string, unknown> ? `${K}.${ObjectPath<T[K]>}` : K) : never
}[keyof T]

declare type Resolve<T, U> = U extends `${infer K}.${infer V}`
  ? K extends keyof T
    ? Resolve<T[K], V>
    : never
  : U extends keyof T
    ? T[U]
    : never

declare type Overwrite<T, U> = Omit<T, keyof U> & U
