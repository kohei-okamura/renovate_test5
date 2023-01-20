<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page data-page-roles-index :breadcrumbs="breadcrumbs">
    <z-data-table :items="roles" :loading="isLoadingRoles" :options="tableOptions">
      <template #item.scope="{ item }">{{ resolveRoleScope(item.scope) }}</template>
      <template #item.isSystemAdmin="{ item }">{{ isSystemAdmin(item.isSystemAdmin) }}</template>
    </z-data-table>
    <z-fab
      v-if="isAuthorized([permissions.createRoles])"
      bottom
      data-fab
      fixed
      nuxt
      right
      to="/roles/new"
      :icon="$icons.add"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent, useCssModule } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveRoleScope } from '@zinger/enums/lib/role-scope'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dataTableOptions } from '~/composables/data-table-options'
import { rolesStoreKey } from '~/composables/stores/use-roles-store'
import { useAsync } from '~/composables/use-async'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { auth } from '~/middleware/auth'
import { Role } from '~/models/role'

export default defineComponent({
  name: 'RolesIndexPage',
  middleware: [auth(Permission.listRoles)],
  setup () {
    const rolesStore = useInjected(rolesStoreKey)
    useAsync(() => rolesStore.getIndex())

    const isSystemAdmin = (isSystemAdmin: boolean) => isSystemAdmin ? '有効' : '無効'

    const style = useCssModule()
    const tableOptions = dataTableOptions<Role>({
      content: 'ロール',
      headers: [
        { text: 'ロール名', value: 'name', class: style.name, align: 'start', sortable: true },
        { text: '権限範囲', value: 'scope', class: style.scope, align: 'start', sortable: true },
        { text: 'システム管理権限', value: 'isSystemAdmin', class: style.isSystemAdmin, align: 'start', sortable: true }
      ],
      itemLink: x => `/roles/${x.id}`,
      itemLinkPermissions: [Permission.viewRoles]
    })

    return {
      ...useAuth(),
      ...useBreadcrumbs('roles.index'),
      ...rolesStore.state,
      isSystemAdmin,
      tableOptions,
      resolveRoleScope
    }
  },
  head: () => ({
    title: 'ロール'
  })
})
</script>

<style lang="scss" module>
.name,
.scope,
.isSystemAdmin {
  width: auto;
}
</style>
