<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-tabs
    v-if="hasTabs"
    v-model="tab"
    active-class="font-weight-bold"
    background-color="transparent"
    centered
    color="accent"
    light
    show-arrows
    :grow="$vuetify.breakpoint.mdAndDown"
  >
    <v-tab v-for="x in tabs" :key="x.to" :to="x.to">{{ x.label }}</v-tab>
  </v-tabs>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { usePlugins } from '~/composables/use-plugins'

export default defineComponent({
  name: 'ZTabs',
  setup () {
    const { $tabs } = usePlugins()
    /*
     * ...tabs だと
     * [Vue warn]: The setup binding property "_isVue" is already declared.
     * が出るので、必要なものだけを取り出す
     */
    const { hasTabs, tabs, tab } = $tabs
    return {
      hasTabs,
      tabs,
      tab
    }
  }
})
</script>
