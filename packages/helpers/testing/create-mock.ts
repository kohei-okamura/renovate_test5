/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

export type MockOptions<T> = {
  [K in keyof T]?: Partial<T[K]>
}

export type Mocked<T> = jest.Mocked<T> & {
  [P in keyof T]: Mocked<T[P]>
}

export function createMock<T> (object: MockOptions<T> = {}): Mocked<T> {
  const xs: Dictionary = {
    ...object
  }
  const get = <K extends PropertyKey & keyof T> (_target: T, key: K) => {
    if (typeof key === 'string') {
      if (key === 'hasOwnProperty') {
        return (key: string) => Object.prototype.hasOwnProperty.call(xs, key)
      } else {
        if (!Object.prototype.hasOwnProperty.call(xs, key)) {
          xs[key] = key === 'then' || key === 'toJSON'
            ? undefined
            : (...args: any[]) => {
              throw new Error(`Unexpected function called: ${key} with args ` + JSON.stringify(args))
            }
        }
        return xs[key]
      }
    } else {
      return undefined
    }
  }
  const has = <K extends PropertyKey & keyof T> (_target: T, key: K) => {
    return Object.prototype.hasOwnProperty.call(xs, key)
  }
  return new Proxy(xs, { get, has } as ProxyHandler<any>)
}
