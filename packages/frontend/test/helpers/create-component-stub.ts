/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { FunctionalComponentOptions } from 'vue'

/**
 * すべてのスロットをレンダリングするコンポーネントスタブを生成する.
 */
export function createComponentStub (name: string, slotNames: string[] = []): FunctionalComponentOptions {
  return {
    name,
    functional: true,
    render (h, { data, slots }) {
      const s = slots()
      return h(
        'div',
        { ...data, class: `${name}-stub` },
        slotNames.map(x => {
          return x === 'default'
            ? s[x]
            : h('div', { class: `${name}-stub-slot-${x}` }, s[x])
        })
      )
    }
  }
}
