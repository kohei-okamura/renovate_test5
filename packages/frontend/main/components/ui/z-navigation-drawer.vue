<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-navigation-drawer
    v-model="isDrawerActive"
    data-navigation-drawer
    v-bind="drawerProps"
  >
    <v-list dense>
      <template v-for="(item, i) in menu">
        <template v-if="isAuthorized(item.permissions)">
          <div v-if="item.children" :key="i">
            <v-list-group :class="$style.group" no-action :group="item.group" :prepend-icon="item.icon">
              <template #activator>
                <v-list-item-content>
                  <v-list-item-title data-menu-item-title>{{ item.text }}</v-list-item-title>
                </v-list-item-content>
              </template>
              <template v-for="(x, j) in item.children">
                <v-list-item v-if="isAuthorized(x.permissions)" :key="j" nuxt router :to="x.to">
                  <v-list-item-content>
                    <v-list-item-title data-menu-item-children-title>{{ x.text }}</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </template>
            </v-list-group>
            <v-divider v-if="item.avatar" class="my-2" />
          </div>
          <v-list-item v-else-if="item.to" :key="i" nuxt router :to="item.to">
            <v-list-item-icon v-if="item.icon">
              <v-icon>{{ item.icon }}</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title data-menu-item-title>{{ item.text }}</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </template>
      </template>
    </v-list>
  </v-navigation-drawer>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useAuth } from '~/composables/use-auth'
import { useMatchMedia } from '~/composables/use-match-media'
import { usePlugins } from '~/composables/use-plugins'
import { Menu } from '~/models/menu'
import { $icons } from '~/plugins/icons'

const DEFAULT_WIDTH = 250

type CreateMenuGroupParams = Pick<Required<Menu.Group>, 'icon' | 'text' | 'children'>
const createMenuGroup = (params: CreateMenuGroupParams): Menu.Group => {
  const group = `^(${params.children.map(x => x.to).join('|')})`
  const permissions = params.children.flatMap(x => x.permissions ?? [])
  return {
    ...params,
    group,
    permissions
  }
}

const createMenu = (): Menu.Element[] => [
  { icon: $icons.dashboard, text: 'ダッシュボード', to: '/dashboard' },
  createMenuGroup({
    icon: $icons.ledger,
    text: '台帳管理',
    children: [
      { text: '利用者', to: '/users', permissions: [Permission.listUsers] },
      { text: 'スタッフ', to: '/staffs', permissions: [Permission.listStaffs] },
      { text: '事業所', to: '/offices', permissions: [Permission.listInternalOffices, Permission.listExternalOffices] },
      { text: '事業所グループ', to: '/office-groups', permissions: [Permission.listOfficeGroups] },
      { text: '自費サービス', to: '/own-expense-programs', permissions: [Permission.listOwnExpensePrograms] }
    ]
  }),
  createMenuGroup({
    icon: $icons.shift,
    text: '勤怠管理',
    children: [
      { text: '勤務シフト', to: '/shifts', permissions: [Permission.listShifts] },
      { text: '勤務実績', to: '/attendances', permissions: [Permission.listAttendances] }
    ]
  }),
  createMenuGroup({
    icon: $icons.provisionReport,
    text: '予実管理',
    children: [
      { text: '障害福祉サービス予実', to: '/dws-provision-reports', permissions: [Permission.listDwsProvisionReports] },
      { text: '介護保険サービス予実', to: '/ltcs-provision-reports', permissions: [Permission.listLtcsProvisionReports] }
    ]
  }),
  createMenuGroup({
    icon: $icons.billing,
    text: '請求管理',
    children: [
      { text: '障害福祉サービス請求', to: '/dws-billings', permissions: [Permission.listBillings] },
      { text: '介護保険サービス請求', to: '/ltcs-billings', permissions: [Permission.listBillings] },
      { text: '利用者請求', to: '/user-billings', permissions: [Permission.listUserBillings] }
    ]
  }),
  createMenuGroup({
    icon: $icons.settings,
    text: '設定',
    children: [
      { text: 'ロール', to: '/roles', permissions: [Permission.listRoles] },
      { text: '事業者別設定', to: '/settings', permissions: [Permission.viewOrganizationSettings] }
    ]
  })
]

export default defineComponent({
  name: 'ZNavigationDrawer',
  setup () {
    const { $drawer, $vuetify } = usePlugins()
    const { hasCoarsePointer } = useMatchMedia()
    const { isAuthorized } = useAuth()
    const canHover = computed(() => !hasCoarsePointer())
    const isWideScreen = computed(() => $vuetify.breakpoint.mdAndUp)
    const isUseHover = computed(() => {
      // sm 以下の時は有効にしない
      if ($vuetify.breakpoint.smAndDown) {
        return false
      }
      // 広い画面かつ hover が使える場合は有効にする
      return isWideScreen.value && canHover.value
    })
    const mobileBreakpoint = computed(() => {
      const { sm, md } = $vuetify.breakpoint.thresholds
      return canHover.value ? sm : md
    })
    const drawerProps = computed(() => ({
      app: true,
      color: 'primary',
      dark: true,
      expandOnHover: isUseHover.value,
      mobileBreakpoint: mobileBreakpoint.value,
      width: isWideScreen.value ? DEFAULT_WIDTH : undefined
    }))
    const isDrawerActive = computed({
      //  hover を使用する時は常にアクティブとして扱う
      get: () => $drawer.isOpened.value || isUseHover.value,
      set: (value: boolean) => $drawer.set(value)
    })
    const menu = createMenu()
    return {
      drawerProps,
      isAuthorized,
      isDrawerActive,
      menu
    }
  }
})
</script>

<style lang="scss" module>
.group:global(.v-list-group.primary--text) {
  color: #f2bb05 !important;
}
</style>
