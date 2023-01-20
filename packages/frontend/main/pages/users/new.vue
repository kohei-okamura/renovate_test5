<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-form button-text="登録" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { useAxios } from '~/composables/use-axios'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { UsersApi } from '~/services/api/users-api'

type Form = Partial<UsersApi.Form>

export default defineComponent({
  name: 'UsersNewPage',
  middleware: [auth(Permission.createUsers)],
  setup () {
    const { $alert, $api, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('users.new'),
      errors,
      progress,
      value: {},
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await $api.users.create({ form })
          await catchErrorStack(() => $router.replace('/users?restore=1'))
          $snackbar.success('利用者情報を登録しました。')
        }),
        (error: Error) => $alert.error('利用者情報の登録に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '利用者を登録'
  })
})
</script>
