<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-app :class="classMap" data-layout-default>
    <z-app-bar
      :number-of-notices="notifications.length"
      @click:bell="toggleNotification"
      @click:nav="hideNotification"
    />
    <v-main>
      <nuxt />
    </v-main>
    <z-navigation-drawer />
    <z-confirm-dialog />
    <v-snackbar v-model="snackbar" top="top" :color="config.color">{{ config.text }}</v-snackbar>
    <z-notifications
      v-if="isDisplayed"
      :items="notifications"
      @click:delete="deleteNotification"
      @click:delete-all="deleteAllNotification"
    />
  </v-app>
</template>

<script lang="ts">
import { computed, defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { notificationStoreKey, ZNotification } from '~/composables/stores/use-notification-store'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useSnackbar } from '~/composables/use-snackbar'

export default defineComponent({
  name: 'DefaultLayout',
  setup () {
    const style = useCssModule()
    const useClassMap = () => {
      const { $vuetify } = usePlugins()
      const classMap = computed(() => ({
        'z-app': true,
        'layout-default': true,
        [style.root]: true,
        'z-app--mobile': $vuetify.breakpoint.xsOnly
      }))
      return { classMap }
    }
    const useNotification = () => {
      const { $snackbar } = usePlugins()
      const notificationStore = useInjected(notificationStoreKey)
      return {
        ...notificationStore.state,
        deleteAllNotification: () => {
          notificationStore.removeCompletionNotifications()
          if (notificationStore.state.hasNotification.value) {
            $snackbar.info('処理中のタスクの通知は消すことができません。終了後に消してください。')
          }
        },
        deleteNotification: (id: ZNotification['id']) => notificationStore.removeNotification(id),
        hideNotification: () => notificationStore.updateIsDisplayed(false),
        toggleNotification: () => notificationStore.toggleIsDisplayed()
      }
    }
    return {
      ...useClassMap(),
      ...useNotification(),
      ...useSnackbar()
    }
  }
})
</script>

<style lang="scss" module>
.root {
  :global(.v-main) {
    transition: unset;
  }
}
</style>
