<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-sheet data-z-breadcrumbs class="d-flex" :class="$style.breadcrumbs" height="32" light>
    <v-breadcrumbs divider="▸" :items="breadcrumbs">
      <template #item="{ item }">
        <v-breadcrumbs-item
          exact
          nuxt
          :disabled="item.disabled"
          :to="item.to"
        >
          {{ item.text }}
        </v-breadcrumbs-item>
      </template>
    </v-breadcrumbs>
  </v-sheet>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { usePlugins } from '~/composables/use-plugins'

export default defineComponent({
  name: 'ZBreadcrumbs',
  setup () {
    const { $breadcrumbs } = usePlugins()
    /*
     * ...$breadcrumbs だと
     * [Vue warn]: The setup binding property "_isVue" is already declared.
     * が出るので、必要なものだけを取り出す
     */
    const { breadcrumbs } = $breadcrumbs
    return {
      breadcrumbs
    }
  }
})
</script>

<style lang="scss" module>
.breadcrumbs {
  :global(.v-breadcrumbs) {
    padding: 0 16px;
  }
}
</style>
