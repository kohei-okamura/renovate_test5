/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { defineComponent, h, useCssModule } from '@nuxtjs/composition-api'
import { VNodeData } from 'vue'
import { ClassMap } from '~/models/vue'

type DefineDivComponentParams = {
  name: string
  classMap: ClassMap
  useCssModule?: true
}
/**
 * CSS Modulesと指定したクラスを属性として持つコンポーネントを定義する.
 */
export const defineDivComponent = (params: DefineDivComponentParams) => defineComponent({
  name: params.name,
  setup (_, context) {
    const classMap = params.useCssModule
      ? Object.fromEntries(Object.entries(useCssModule()).map(x => [x[1], true]))
      : undefined
    const data: VNodeData = {
      ...context.attrs,
      class: {
        ...classMap,
        ...params.classMap
      }
    }
    return () => h('div', data, context.slots.default ? context.slots?.default() : [])
  }
})
