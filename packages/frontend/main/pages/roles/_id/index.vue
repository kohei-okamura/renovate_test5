<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs" :title="role.name">
    <z-data-card title="基本情報">
      <template #menu></template>
      <z-data-card-item label="ロール名" :icon="$icons.role" :value="role.name" />
      <z-data-card-item label="権限範囲" :icon="$icons.scope" :value="resolveRoleScope(role.scope)" />
      <z-data-card-item label="システム管理権限" :icon="$icons.admin" :value="role.isSystemAdmin ? '有効' : '無効'" />
      <template v-if="!role.isSystemAdmin">
        <v-divider />
        <v-subheader>詳細権限</v-subheader>
        <z-data-card-item v-for="g in permissionGroups" :key="g.id">
          <template #label>権限：{{ g.displayName }}</template>
          <v-chip v-for="p in g.permissions" :key="p" color="secondary" label small :disabled="!isPermissionEnabled(p)">
            {{ resolvePermission(p) }}
          </v-chip>
        </z-data-card-item>
      </template>
    </z-data-card>
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateRoles, permissions.deleteRoles])"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.updateRoles])"
        :icon="$icons.edit"
        :to="`/roles/${role.id}/edit`"
      >
        ロールを編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="isAuthorized([permissions.deleteRoles])"
        :icon="$icons.delete"
        @click.prevent="deleteRole"
      >
        ロールを削除
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission, resolvePermission } from '@zinger/enums/lib/permission'
import { resolveRoleScope } from '@zinger/enums/lib/role-scope'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { roleStateKey } from '~/composables/stores/use-role-store'
import { useAuth } from '~/composables/use-auth'
import { useDeleteFunction } from '~/composables/use-delete-function'
import { useInjected } from '~/composables/use-injected'
import { usePermissionGroups } from '~/composables/use-permission-groups'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'RolesViewPage',
  middleware: [auth(Permission.viewRoles)],
  setup () {
    const { $api } = usePlugins()
    const { role } = useInjected(roleStateKey)
    return {
      ...useAuth(),
      ...useBreadcrumbs('roles.view', role),
      ...usePermissionGroups(),
      resolvePermission,
      role,
      resolveRoleScope,
      deleteRole: useDeleteFunction(role, x => ({
        messageOnConfirm: `ロール「${x.name}」を削除します。\n\n本当によろしいですか？`,
        messageOnSuccess: 'ロールを削除しました。',
        returnTo: '/roles',
        callback: () => $api.roles.delete({ id: x.id })
      })),
      isPermissionEnabled: (permission: Permission): boolean => {
        const xs = role.value?.permissions ?? []
        return xs.includes(permission)
      }
    }
  },
  head: () => ({
    title: 'ロール詳細'
  })
})
</script>
