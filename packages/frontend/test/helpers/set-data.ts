/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'

export async function setData<T = any> (wrapper: Wrapper<any>, data: Partial<T>): Promise<void> {
  wrapper.setData(data)
  await wrapper.vm.$nextTick()
}
