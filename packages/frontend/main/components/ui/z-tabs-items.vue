<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent, h, SetupContext, useCssModule } from '@nuxtjs/composition-api'
import { VNodeData } from 'vue'
import { VTabsItems } from 'vuetify/lib/components'

function getData (context: SetupContext, className: string): VNodeData {
  return {
    props: {
      ...context.attrs
    },
    staticClass: `${className}`
  }
}

export default defineComponent({
  name: 'ZTabsItems',
  setup (_, context) {
    const style = useCssModule()
    return () => h(
      VTabsItems,
      getData(context, style.adjustEdge),
      context.slots.default ? context.slots?.default() : []
    )
  }
})
</script>

<style lang="scss" module>
.adjustEdge {
  margin-bottom: -3px;
  margin-left: -3px;
  margin-right: -3px;
  max-width: initial; // margin-right のネガティブマージンを効かせるために必要
  padding-bottom: 3px;
  padding-left: 3px;
  padding-right: 3px;
}
</style>
