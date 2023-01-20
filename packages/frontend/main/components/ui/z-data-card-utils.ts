/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { h, SetupContext } from '@nuxtjs/composition-api'
import { VNode } from 'vue'
import { VCard, VCardActions, VCardText } from 'vuetify/lib'
import ZDataCardHeader from '~/components/ui/z-data-card-header.vue'
import ZSubheader from '~/components/ui/z-subheader.vue'

type Props = {
  title: string
}

export const renderDataCard = (name: string, context: SetupContext, props: Props): VNode => {
  const a = context.attrs
  const p = props
  const s = context.slots
  const data = {
    ...a,
    staticClass: a.staticClass ? `${name} ${a.staticClass}` : name
  }
  return h('div', data, [
    !p.title ? [] : h(ZSubheader, p.title),
    h(VCard, { staticClass: 'flex-grow-1' }, [
      s.header && h(ZDataCardHeader, s.header()),
      h(VCardText, { class: 'px-0' }, s.default ? s.default() : []),
      s.actions && h(VCardActions, s.actions())
    ])
  ])
}
