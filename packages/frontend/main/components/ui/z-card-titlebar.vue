<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent, h, SetupContext, useCssModule } from '@nuxtjs/composition-api'
import { VNodeData } from 'vue'
import { VSheet } from 'vuetify/lib'

const sheetData = (context: SetupContext, className: string): VNodeData => ({
  props: {
    ...context.attrs,
    dark: true,
    flat: true,
    tile: true
  },
  staticClass: `${className} d-flex align-center`
})

const titleData = (className: string): VNodeData => ({
  staticClass: className
})

export default defineComponent({
  name: 'ZCardTitlebar',
  setup (_, context) {
    const style = useCssModule()
    return () => h(VSheet, sheetData(context, style.titlebar), [
      h('div', titleData(style.title), context.slots.default ? context.slots?.default() : [])
    ])
  }
})
</script>

<style lang="scss" module>
.titlebar {
  height: 64px;
}

.title {
  padding: 4px 16px;
}
</style>
