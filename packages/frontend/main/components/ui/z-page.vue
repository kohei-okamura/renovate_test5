<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div :class="classMap" data-z-page>
    <v-container class="z-page__content">
      <v-alert v-model="alertShow" dismissible elevation="1" :type="alert.color">
        <h3>{{ alert.title }}</h3>
        <div class="alert-body">{{ alert.text }}</div>
      </v-alert>
      <slot name="default"></slot>
    </v-container>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, useCssModule, watchEffect } from '@nuxtjs/composition-api'
import { usePlugins } from '~/composables/use-plugins'
import { VBreadcrumb, VTab } from '~/models/vuetify'

type Props = Readonly<{
  breadcrumbs: VBreadcrumb[]
  compact: boolean
  fluid: boolean
  tabs: VTab[]
}>

export default defineComponent<Props>({
  name: 'ZPage',
  props: {
    breadcrumbs: { type: Array, default: () => [] },
    compact: { type: Boolean, default: false },
    fluid: { type: Boolean, default: false },
    tabs: { type: Array, default: () => [] }
  },
  setup (props) {
    const { $alert, $breadcrumbs, $tabs, $vuetify } = usePlugins()
    const { config: alert, alertShow } = $alert
    const style = useCssModule()
    const classMap = computed(() => ({
      [style.root]: true,
      [style.compact]: props.compact,
      [style.fluid]: props.fluid,
      'z-page--mobile': $vuetify.breakpoint.smAndDown
    }))
    watchEffect(
      () => $breadcrumbs.update(props.breadcrumbs),
      { flush: 'sync' }
    )
    watchEffect(
      () => $tabs.update(props.tabs),
      { flush: 'sync' }
    )
    return {
      alert,
      alertShow,
      classMap
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/settings/variables';

.root {
  padding: 0 0 72px;

  &.compact {
    :global {
      & .container,
      & .v-card,
      & .v-subheader {
        max-width: 712px;
      }
    }
  }

  &.fluid {
    :global {
      & .container {
        max-width: 100%;
      }
    }
  }
}
</style>
