<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-role-form button-text="保存" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { roleStateKey, roleStoreKey } from '~/composables/stores/use-role-store'
import { rolesStoreKey } from '~/composables/stores/use-roles-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { Role } from '~/models/role'
import { RolesApi } from '~/services/api/roles-api'

type Form = Partial<RolesApi.Form>

export default defineComponent({
  name: 'RolesEditPage',
  middleware: [auth(Permission.updateRoles)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { role } = useInjected(roleStateKey)
    const roleStore = useInjected(roleStoreKey)
    const rolesStore = useInjected(rolesStoreKey)
    const createFormValue = (x: Role): Form => ({
      name: x.name,
      isSystemAdmin: x.isSystemAdmin,
      permissions: Object.fromEntries(x.permissions.map(x => [x, true])),
      scope: x.scope
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('roles.edit', role),
      errors,
      progress,
      value: createFormValue(role.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = role.value!.id
          await roleStore.update({ form, id })
          await catchErrorStack(async () => {
            await $router.replace('/roles')
            await rolesStore.getIndex()
          })
          $snackbar.success('ロールを編集しました。')
        }),
        (error: Error) => $alert.error('ロールの編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: 'ロールを編集'
  })
})
</script>
