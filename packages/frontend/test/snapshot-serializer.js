/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { format, plugins } from 'pretty-format'

const isVueWrapper = received => received && typeof received === 'object' && typeof received.isVueInstance === 'function'
const regexp = /^\s+(?:aria-owns="list-\d+"|name="radio-\d+"|(?:aria-labelledby|for|id)="input-\d+"|)\n/gm

/**
 * 自作 Snapshot Serializer.
 *
 * Vuetify コンポーネントが自動で付与する属性がうざすぎるので取り除く.
 */
module.exports = {
  test (received) {
    return received instanceof HTMLElement || isVueWrapper(received)
  },
  print (received) {
    const html = format(received instanceof HTMLElement ? received : received.element, {
      plugins: [plugins.DOMElement]
    })
    return html.replace(regexp, '')
  }
}
