/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * The assert function.
 */
export function assert (condition: boolean, message: string): asserts condition {
  if (!condition) {
    throw new Error(message)
  }
}
