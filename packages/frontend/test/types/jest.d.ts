/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare namespace jest {
  export interface Each {
    <A> (cases: Array<[A]>): (name: string, fn: (a: A) => any, timeout?: number) => void
    <A, B> (cases: Array<[A, B]>): (name: string, fn: (a: A, b: B) => any, timeout?: number) => void
    <A, B, C> (cases: Array<[A, B, C]>): (name: string, fn: (a: A, b: B, c: C) => any, timeout?: number) => void
  }

  export interface Matchers<R> {
    toBeArray (): R
    toBeDisabled (): R
    toBeEmptyArray (): R
    toBeFalse (): R
    toBeFunction (): R
    toBePassed (): R
    toBeRef (): R
    toBeRefTo (value: any): R
    toBeTrue (): R
    toContainElement (element: any/* string | Component */): R
    toExist (): R
    toHaveBeenErrored (): R
    toHaveBeenWarned (): R
  }
}
