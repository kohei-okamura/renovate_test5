<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-menu offset-y>
    <template #activator="{ on, attrs }">
      <v-btn class="px-2" height="48" text tile v-bind="attrs" v-on="on">
        <v-avatar size="24">
          <v-img data-z-app-bar-avatar :src="avatar" />
        </v-avatar>
        <span v-if="isNotMobile" class="ml-2">{{ displayName }}</span>
        <v-icon right>{{ $icons.expand }}</v-icon>
      </v-btn>
    </template>
    <v-list dense>
      <v-list-item
        v-for="(item, i) in menu"
        :key="i"
        :disabled="item.disabled"
        @click="() => clickListItem(item.to, item.action)"
      >
        <v-list-item-content>
          <v-list-item-title>{{ item.text }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
      <v-list-item v-if="isAdmin">
        <v-list-item-content>
          <v-list-item-title>開発者モード</v-list-item-title>
          <v-switch
            v-model="isDevMode"
            class="px-2 py-1"
            inset
            :label="isDevMode ? '有効' : '無効'"
          />
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script lang="ts">
import { computed, ComputedRef, defineComponent } from '@nuxtjs/composition-api'
import Gravatar from 'gravatar'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useInjected } from '~/composables/use-injected'
import { useNotificationApi } from '~/composables/use-notification-api'
import { useObservableLocalStorage } from '~/composables/use-observable-local-storage'
import { usePlugins } from '~/composables/use-plugins'
import { Menu } from '~/models/menu'

export default defineComponent({
  name: 'ZAvatarMenu',
  setup () {
    const { $router, $vuetify } = usePlugins()
    const sessionStore = useInjected(sessionStoreKey)
    const { auth } = sessionStore.state
    const { name: { displayName }, email } = auth.value?.staff ?? { name: { displayName: '-' }, email: '' }
    const avatar = computed(() => Gravatar.url(email, { default: 'mp', size: '64' }))
    const isNotMobile = computed(() => $vuetify.breakpoint.mdAndUp)
    const clickListItem = async (to?: string, fn?: () => Promise<void>) => {
      fn && await fn()
      to && await $router.push(to)
    }
    const logout = async () => {
      await catchErrorStack(() => sessionStore.destroy())
    }
    const notification = useNotificationApi()
    const menu: ComputedRef<Menu.Element[]> = computed(() => [
      { text: '登録情報を編集', to: '/profile' },
      {
        text: notification.statusText.value,
        action: notification.askPermission,
        disabled: notification.isAlreadyConfirmed.value
      },
      { text: 'ログアウト', to: '/', action: logout }
    ])
    // 開発者モード
    const isDevMode = useObservableLocalStorage('developer-mode', false)
    return {
      avatar,
      clickListItem,
      displayName,
      isNotMobile,
      isAdmin: computed(() => auth.value?.isSystemAdmin ?? false),
      isDevMode,
      menu
    }
  }
})
</script>
