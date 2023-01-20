/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { noop } from '@zinger/helpers'
import { catchErrorStack } from '~/composables/catch-error-stack'

export const useBackgroundLoader = <T> (f: () => Promise<T>, onSuccess: (x: T) => void = noop): void => {
  catchErrorStack(() => f().then(onSuccess))
}
