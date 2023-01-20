<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent, h } from '@nuxtjs/composition-api'
import { assert } from '@zinger/helpers'

type Props<T = any> = {
  value: Promise<T>
  tag: string
}

export default defineComponent<Props>({
  name: 'ZWith',
  props: {
    value: { type: [String, Number, Boolean, Array, Object], required: true },
    tag: { type: String, default: 'div' }
  },
  setup (props, { slots }) {
    return () => {
      assert(slots.default !== undefined, 'default slot is not defined')
      return h(props.tag, slots.default({ data: props.value }))
    }
  }
})
</script>
