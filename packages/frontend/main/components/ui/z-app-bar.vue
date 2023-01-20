<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-app-bar
    app
    data-z-app-bar
    :class="$style.root"
    color="secondary"
    dark
    dense
    elevate-on-scroll
    extended
    fixed
    :extension-height="extensionHeight"
  >
    <v-app-bar-nav-icon
      v-if="needNav"
      class="z-app-bar__menu-button"
      data-menu-button
      @click.stop="open"
    />
    <v-toolbar-title>
      <nuxt-link ref="logo" :class="$style.title" to="/dashboard">careid</nuxt-link>
    </v-toolbar-title>
    <v-spacer />
    <z-notification-icon
      data-notification-icon
      :number-of-notices="numberOfNotices"
      @click="$emit('click:bell', $event)"
    />
    <z-avatar-menu />
    <template #extension>
      <div class="align-self-stretch flex-grow-1" :class="$style.extension">
        <z-breadcrumbs />
        <z-tabs />
      </div>
    </template>
  </v-app-bar>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { useMatchMedia } from '~/composables/use-match-media'
import { usePlugins } from '~/composables/use-plugins'

const BREADCRUMBS_HEIGHT = 32
const TABS_HEIGHT = 48

export default defineComponent({
  name: 'ZAppBar',
  props: {
    numberOfNotices: { type: Number, default: 0 }
  },
  setup (_, context) {
    const { $drawer, $tabs, $vuetify } = usePlugins()
    const { hasCoarsePointer } = useMatchMedia()
    const needNav = computed(() => $vuetify.breakpoint.smAndDown || hasCoarsePointer())
    const extensionHeight = computed(() => {
      return BREADCRUMBS_HEIGHT + ($tabs.hasTabs.value ? TABS_HEIGHT : 0)
    })
    return {
      extensionHeight,
      needNav,
      open: () => {
        context.emit('click:nav')
        $drawer.set(true)
      }
    }
  }
})
</script>

<style lang="scss" module>
.root {
  :global(.v-toolbar__extension) {
    padding: 0;
  }

  .title {
    color: inherit;
    font-family: 'Rajdhani', 'Roboto', sans-serif;
    text-decoration: none;
  }
}

.extension {
  background-color: #fff;
}
</style>
