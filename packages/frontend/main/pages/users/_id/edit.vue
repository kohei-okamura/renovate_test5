<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-form button-text="保存" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { userStateKey, userStoreKey } from '~/composables/stores/use-user-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { User } from '~/models/user'
import { UsersApi } from '~/services/api/users-api'

type Form = Partial<UsersApi.Form>

export default defineComponent({
  name: 'UsersEditPage',
  middleware: [auth(Permission.updateUsers)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { user } = useInjected(userStateKey)
    const userStore = useInjected(userStoreKey)
    const createFormValue = (x: User): Form => ({
      ...x.name,
      sex: x.sex,
      birthday: x.birthday,
      ...x.addr,
      contacts: x.contacts,
      isEnabled: x.isEnabled,
      billingDestination: x.billingDestination
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('users.edit', user),
      errors,
      progress,
      value: createFormValue(user.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = user.value!.id
          await userStore.update({ form, id })
          await catchErrorStack(() => $router.replace(`/users/${id}#user`))
          $snackbar.success('利用者基本情報を編集しました。')
        }),
        (error: Error) => $alert.error('利用者基本情報の編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '利用者基本情報を編集'
  })
})
</script>
