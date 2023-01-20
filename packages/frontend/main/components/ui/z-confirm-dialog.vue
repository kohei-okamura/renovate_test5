<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-prompt-dialog
    :active="active"
    :in-progress="data.progress"
    :options="options"
    @click:negative="onNegativeClicked"
    @click:positive="onPositiveClicked"
  />
</template>

<script lang="ts">
import { defineComponent, reactive } from '@nuxtjs/composition-api'
import { usePlugins } from '~/composables/use-plugins'

export default defineComponent({
  name: 'ZConfirmDialog',
  setup () {
    const { $confirm } = usePlugins()
    const data = reactive({
      progress: false
    })
    const close = (result: boolean) => () => {
      data.progress = true
      $confirm.resolve(result)
      $confirm.hide()
      data.progress = false
    }
    return {
      data,
      active: $confirm.active,
      options: $confirm.options,
      onNegativeClicked: close(false),
      onPositiveClicked: close(true)
    }
  }
})
</script>
