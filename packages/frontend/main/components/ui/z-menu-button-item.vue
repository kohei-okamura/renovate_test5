<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { VNode, VNodeData } from 'vue'
import { VIcon, VListItem, VListItemContent, VListItemIcon, VListItemTitle } from 'vuetify/lib'
import { defineFunctionalComponent } from '~/components/tools/define-functional-component'

export default defineFunctionalComponent({
  name: 'ZMenuButtonItem',
  functional: true,
  props: {
    icon: { type: String, default: '' },
    to: { type: String, default: '' }
  },
  render (h, context): VNode {
    const props = context.props
    const data: VNodeData = {
      on: context.listeners,
      props: {
        nuxt: props.to !== '',
        to: props.to
      }
    }
    return h(VListItem, data, [
      h(VListItemIcon, [
        props.icon && h(VIcon, props.icon)
      ]),
      h(VListItemContent, [
        h(VListItemTitle, context.children)
      ])
    ])
  }
})
</script>
