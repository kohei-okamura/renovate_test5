/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export const catchErrorStack = async <T> (f: () => Promise<T>) => {
  try {
    return await f()
  } catch (error) {
    window.console.error(error)
  }
}
