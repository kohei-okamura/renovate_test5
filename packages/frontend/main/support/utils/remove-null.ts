/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type RemoveNull = {
  (x: null): undefined
  <T> (x: T): T
  <T> (x: T[]): T[]
}

/**
 * null を取り除く
 * val が null の場合は undefined を返す
 * val が Array の場合は null を undefined に変換した Array を返す
 * val が Object の場合は 値が null のプロパティを削除した Object を返す
 *
 * @param val: 値
 * @example
 *  removeNull(null) //=> undefined
 * @example
 *  removeNull([1, undefined, 'foo', null]) //=> [1, undefined, 'foo', undefined]
 * @example
 *  removeNull({ foo: undefined, bar: null }) //=> { foo: undefined }
 * @example
 *  removeNull([
 *    {
 *      bar: '',
 *      baz: null,
 *      qux: 10,
 *      quux: {
 *        corge: 'aiueo',
 *        grault: 100,
 *        garply: null,
 *        waldo: {
 *          fred: null,
 *          plugh: undefined,
 *          xyzzy: 1000,
 *          thud: 'kakikukeko'
 *        }
 *      }
 *    },
 * ])
 * //=> [
 *        {
 *          bar: '',
 *          qux: 10,
 *          quux: {
 *            corge: 'aiueo',
 *            grault: 100,
 *            waldo: {
 *              plugh: undefined,
 *              xyzzy: 1000,
 *              thud: 'kakikukeko'
 *            }
 *          }
 *        },
 *      ]
 */
export const removeNull: RemoveNull = (x: any) => {
  if (x === null) {
    return undefined
  }
  if (Array.isArray(x)) {
    return x.map(x => removeNull(x))
  }
  if (typeof x === 'object') {
    return Object.fromEntries(
      Object.entries(x).filter(([_, v]) => v !== null).map(([k, v]) => [k, removeNull(v)])
    )
  }
  return x
}
