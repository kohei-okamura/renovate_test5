<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-role-form button-text="登録" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { rolesStoreKey } from '~/composables/stores/use-roles-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { RolesApi } from '~/services/api/roles-api'

type Form = Partial<RolesApi.Form>

export default defineComponent({
  name: 'RolesNewPage',
  middleware: [auth(Permission.createRoles)],
  setup () {
    const { $alert, $api, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const rolesStore = useInjected(rolesStoreKey)
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('roles.new'),
      errors,
      progress,
      value: {},
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await $api.roles.create({ form })
          await catchErrorStack(async () => {
            await rolesStore.getIndex()
            await $router.replace('/roles')
          })
          $snackbar.success('ロールを登録しました。')
        }),
        (error: Error) => $alert.error('ロールの登録に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: 'ロールを登録'
  })
})
</script>
