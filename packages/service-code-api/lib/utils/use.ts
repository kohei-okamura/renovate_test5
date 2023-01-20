/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type Closable<T> = {
  close (): T
}

type Callback<T, U extends Closable<T>> = (x: U) => void | Promise<void>

export const use = <T, U extends Closable<T>> (closable: U) => async (f: Callback<T, U>): Promise<void> => {
  await f(closable)
  closable.close()
}
