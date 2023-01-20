/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, isRef, ref, Ref, toRefs } from '@nuxtjs/composition-api'
import Vue from 'vue'
import { ValidationObserverInstance } from '~/support/validation/types'

export type RefOrValue<T> = Ref<T> | T

export type RefOrValues<T> = {
  [K in keyof T]: RefOrValue<T[K]>
}

export type Refs<Data> = {
  [K in keyof Data]: Data[K] extends Ref<infer V> ? Ref<V> : Ref<Data[K]>
}

export type ReadonlyRefs<T extends Record<string, unknown>> = {
  readonly [K in keyof T]: Readonly<Ref<T[K]>>
}

export const unref = <T> (x: RefOrValue<T>): T => isRef(x) ? x.value : x

export const computedWith = <T, R> (x: RefOrValue<T>, f: (x: T) => R): Ref<R> => computed(() => f(unref(x)))

/**
 * computed を返す。
 * S[K] が Array の時に使用する。
 *
 * @param x オブジェクト
 * @param key 関数に渡したいプロパティのキー
 * @param f S[K] が変わった時に実行する関数
 * @return ComputedRef<R>
 * @example
 *  computedWithForArray(state, 'staffs', (staffs) => {})
 */
export const computedWithForArray = <S extends Record<string, unknown>, K extends keyof S, R> (
  x: S,
  key: K,
  f: (x: S[K]) => R
): Ref<R> => {
  return computed(() => f(unref(x[key])))
}

export const toReadonlyRefs = <T extends Record<string, unknown>> (object: T): ReadonlyRefs<T> => {
  const get = (target: T, key: keyof T & string) => target[key]
  const set = (_: T, key: keyof T & string) => {
    throw new Error(`DO NOT ACCESS STATE[${key}] FROM OUT OF STORE`)
  }
  const proxy = new Proxy(object, { get, set })
  return toRefs(proxy) as ReadonlyRefs<T>
}

/**
 * reactive() で作成した配列の内容を更新する
 * ！target の内容が変わるので注意
 *
 * @param target 変更対象の配列
 * @param newValues 新しい値の配列
 * @example
 *  updateReactiveArray(state.array, newArray)
 */
export const updateReactiveArray = <T> (target: T[], newValues: T[]) => {
  target.splice(0, target.length, ...newValues)
}

/**
 * テンプレート中の要素への参照を作成する.
 */
export const templateRef = <T> () => ref<T>()

/**
 * テンプレート中のコンポーネントへの参照を作成する.
 */
export const componentRef = <V extends Vue = Vue> () => ref<V>()

/**
 * テンプレート中の ValidationObserver への参照を作成する.
 */
export const observerRef = () => ref<ValidationObserverInstance>()
