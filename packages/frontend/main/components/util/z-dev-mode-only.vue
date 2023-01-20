<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<script lang="ts">
import { defineComponent, h } from '@nuxtjs/composition-api'
import { assert } from '@zinger/helpers'
import { VNode } from 'vue'
import { useObservableLocalStorage } from '~/composables/use-observable-local-storage'

export default defineComponent({
  name: 'ZDevModeOnly',
  setup (_, { slots }) {
    const isDevMode = useObservableLocalStorage('developer-mode', false, { writeDefaults: false })
    const getSlotRoot = (): VNode[] => {
      assert(slots.default !== undefined, 'default slot is not given')
      return slots.default()
    }
    // TODO: 余計な div ができるので消したい
    return () => isDevMode.value ? h('div', getSlotRoot()) : undefined
  }
})
</script>
