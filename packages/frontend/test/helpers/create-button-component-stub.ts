/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { FunctionalComponentOptions, VNode } from 'vue'

export function createButtonComponentStub (name: string): FunctionalComponentOptions {
  return {
    name: 'VBtnStub',
    functional: true,
    render: (h, { data, props, slots }): VNode => h(props.tag ?? 'a', { class: name, ...data }, slots().default)
  }
}
