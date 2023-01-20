/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type Params = {
  wait: number
}
export const debounce = <T extends any[]> ({ wait }: Params, f: (...args: T) => void) => {
  let timer: ReturnType<typeof setTimeout> | undefined
  return (...args: T): void => {
    timer && clearTimeout(timer)
    timer = setTimeout(() => f(...args), wait)
  }
}
