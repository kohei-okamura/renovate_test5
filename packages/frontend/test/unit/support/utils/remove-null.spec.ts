/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { removeNull } from '~/support/utils/remove-null'

describe('support/utils/remove-null', () => {
  it.each([
    ['string', 'String Value'],
    ['number', 100],
    ['boolean', false]
  ])('should return as is if value is %s type.', (_, val) => {
    expect(removeNull(val)).toEqual(val)
  })

  it.each([
    ['array', []],
    ['object', {}]
  ])('should return as is if value is empty %s.', (_, val) => {
    expect(removeNull(val)).toEqual(val)
  })

  it('should return undefined if value is null.', () => {
    const origin = null
    const expected = undefined
    expect(removeNull(origin)).toEqual(expected)
  })

  it('should convert null to undefined if value is array.', () => {
    const origin = [1, null, 2, undefined]
    const expected = [1, undefined, 2, undefined]
    expect(removeNull(origin)).toEqual(expected)
  })

  it('should remove null property if value is object.', () => {
    const origin = { foo: 'FOO', bar: null, baz: undefined, qux: 100 }
    const expected = { foo: 'FOO', baz: undefined, qux: 100 }
    expect(removeNull(origin)).toEqual(expected)
  })

  it('should be processed recursively if value is a nested object.', () => {
    const origin = {
      foo: 'FOO',
      bar: null,
      baz: [1, null, 2, undefined],
      qux: {
        quux: 'QUUX',
        corge: null,
        grault: [null, 'GRAULT', undefined, 'grault'],
        garply: undefined,
        waldo: [
          { foo: 'Foo', bar: null, baz: -1, qux: undefined },
          { foo: null, bar: 'Bar', baz: undefined, qux: 1 }
        ],
        fred: {
          plugh: null,
          xyzzy: undefined,
          thud: 'THUD',
          foo: 100
        }
      }
    }
    const expected = {
      foo: 'FOO',
      baz: [1, undefined, 2, undefined],
      qux: {
        quux: 'QUUX',
        grault: [undefined, 'GRAULT', undefined, 'grault'],
        garply: undefined,
        waldo: [
          { foo: 'Foo', baz: -1, qux: undefined },
          { bar: 'Bar', baz: undefined, qux: 1 }
        ],
        fred: {
          xyzzy: undefined,
          thud: 'THUD',
          foo: 100
        }
      }
    }
    expect(removeNull(origin)).toEqual(expected)
  })
})
