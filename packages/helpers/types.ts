/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export type Cast<T, U> = T extends U ? T : never

export type Merge<T> = {
  [K in keyof T]: T[K]
}

/**
 * 大文字.
 */
export type UppercaseChar = 'A'
  | 'B'
  | 'C'
  | 'D'
  | 'E'
  | 'F'
  | 'G'
  | 'H'
  | 'I'
  | 'J'
  | 'K'
  | 'L'
  | 'M'
  | 'N'
  | 'O'
  | 'P'
  | 'Q'
  | 'R'
  | 'S'
  | 'T'
  | 'U'
  | 'V'
  | 'W'
  | 'X'
  | 'Y'
  | 'Z'

/**
 * 総和型（ユニオン型）を交差型に変換するユーティリティ型.
 */
export type UnionToIntersection<T> = (T extends T ? (a: T) => void : never) extends (a: infer V) => void
  ? V
  : never

/**
 * 総和型（ユニオン型）から（おそらく末尾の）単一の型を抽出するユーティリティ型.
 */
export type ExtractFromUnion<T> = UnionToIntersection<T extends T ? (x: T) => 0 : never> extends (x: infer U) => 0
  ? U
  : never

/**
 * 総和型（ユニオン型）をタプル型に変換するユーティリティ型.
 */
export type UnionToTuple<T> = [T] extends [never]
  ? []
  : ExtractFromUnion<T> extends infer U
    ? [...UnionToTuple<Exclude<T, U>>, U]
    : never

/**
 * 文字列を置換する.
 */
export type StringReplace<Search extends string, Replacement extends string, Subject extends string> =
  Subject extends `${infer A}${Search}${infer B}`
    ? `${A}${Replacement}${StringReplace<Search, Replacement, B>}`
    : Subject

/**
 * camelCase の文字列を snake_case に変換する.
 */
export type CamelToSnake<T extends string> = T extends `${infer A}${UppercaseChar}${infer B}`
  ? T extends `${A}${infer U}${B}`
    ? Lowercase<`${A}_${U}${CamelToSnake<B>}`>
    : never
  : T

/**
 * snake_case の文字列を camelCase に変換する.
 */
export type SnakeToCamel<T extends string> = T extends `${infer A}_${infer B}`
  ? `${Lowercase<A>}${Capitalize<SnakeToCamel<B>>}`
  : T

/**
 * オブジェクトのキーを再帰的に `a.b.c` 形式で取得するユーティリティ型.
 */
export type ObjectPath<T> = {
  [K in keyof T]: K extends string ? (T[K] extends Record<string, unknown> ? `${K}.${ObjectPath<T[K]>}` : K) : never
}[keyof T]

/**
 * オブジェクトのパスを指定して対応する型を得るユーティリティ型.
 */
export type ResolveObjectPath<T, U extends string> = U extends `${infer K}.${infer V}`
  ? K extends keyof T
    ? ResolveObjectPath<T[K], V>
    : never
  : U extends keyof T
    ? T[U]
    : never
