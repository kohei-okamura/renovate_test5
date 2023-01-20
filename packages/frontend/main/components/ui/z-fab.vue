<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { RenderContext, VNode, VNodeData } from 'vue'
import { VBtn, VFabTransition, VIcon } from 'vuetify/lib'
import { defineFunctionalComponent } from '~/components/tools/define-functional-component'

function transitionData (): VNodeData {
  return {
    props: {
      leaveAbsolute: true
    }
  }
}

function buttonData (context: RenderContext): VNodeData {
  return {
    ...context.data,
    staticClass: 'z-fab',
    props: {
      color: 'accent',
      dark: true,
      fab: true,
      ...context.props,
      icon: false
    }
  }
}

export default defineFunctionalComponent({
  name: 'ZFab',
  functional: true,
  props: {
    icon: { type: String, required: true },
    to: { type: String, default: undefined }
  },
  render (h, context): VNode {
    return h(VFabTransition, transitionData(), [
      h(VBtn, buttonData(context), [
        h(VIcon, context.props.icon)
      ])
    ])
  }
})
</script>
