/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { confirm } from '~aws/scripts/utils/confirm'
import { onError } from '~aws/scripts/utils/on-error'

export const withConfirm = <T> (message: string, f: () => T): () => Promise<T> => {
  return async () => await confirm(message) ? await f() : onError('canceled.')
}
