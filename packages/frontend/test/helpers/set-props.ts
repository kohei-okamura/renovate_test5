/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'

export async function setProps (wrapper: Wrapper<any>, props: Record<string, any>): Promise<void> {
  wrapper.setProps(props)
  await wrapper.vm.$nextTick()
}
