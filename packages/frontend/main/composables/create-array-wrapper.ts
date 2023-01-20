/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type ArrayWrapper<T> = {
  items: T[]
  keys: number[]
  push (x: T): void
  remove (index: number): void
  replace (oldIndex: number, newIndex: number): void
}

export function createArrayWrapper<T> (xs: T[] | undefined): ArrayWrapper<T> {
  let index = 0
  const items: T[] = xs ?? []
  const keys: number[] = items.map(() => index++)
  return {
    items,
    keys,
    push (x: T): void {
      items.push(x)
      keys.push(index++)
    },
    remove (index: number): void {
      items.splice(index, 1)
      keys.splice(index, 1)
    },
    replace (oldIndex, newIndex) {
      const movedItem = items.splice(oldIndex, 1)[0]
      items.splice(newIndex, 0, movedItem)
      const movedKey = keys.splice(oldIndex, 1)[0]
      keys.splice(newIndex, 0, movedKey)
    }
  }
}
