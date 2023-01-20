<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent, h, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { assert } from '@zinger/helpers'
import { VNode } from 'vue'

type Props<T = any> = {
  promise: Promise<T>
  tag: string
}

export default defineComponent<Props>({
  name: 'ZPromised',
  props: {
    promise: { type: Promise, required: true },
    tag: { type: String, default: 'div' }
  },
  setup (props: Props, { slots }) {
    const reactiveProps = toRefs(props)
    const state = reactive({
      data: undefined as any,
      error: undefined as any,
      isPending: false
    })
    watch(
      reactiveProps.promise,
      promise => {
        state.isPending = true
        state.error = undefined
        promise.then(value => {
          state.isPending = false
          state.data = value
        }).catch(error => {
          state.isPending = false
          state.error = error
        })
      },
      { immediate: true }
    )
    const getSlotContent = (): VNode[] => {
      assert(slots.default !== undefined, 'default slot is not given')
      if (state.isPending) {
        return slots.pending ? slots.pending() : slots.default({ data: undefined })
      } else if (state.error) {
        return slots.rejected ? slots.rejected({ error: state.error }) : slots.default({ data: undefined })
      } else {
        return slots.default({ data: state.data })
      }
    }
    return () => h(reactiveProps.tag.value, getSlotContent())
  }
})
</script>
