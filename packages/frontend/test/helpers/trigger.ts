/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import flushPromises from 'flush-promises'

const createTrigger = (eventName: string) => async (f: () => Wrapper<any>): Promise<void> => {
  f().trigger(eventName)
  await flushPromises()
  jest.runOnlyPendingTimers()
}
export const blur = createTrigger('blur')
export const click = createTrigger('click')
export const focus = createTrigger('focus')
export const submit = createTrigger('submit')
