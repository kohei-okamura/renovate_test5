<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-bank-account-form :errors="errors" :progress="progress" :value="value" @submit="submit" />
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
import { BankAccount } from '~/models/bank-account'
import { UserId } from '~/models/user'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'

type Form = Partial<BankAccountsApi.Form>

export default defineComponent({
  name: 'UsersBankAccountEditPage',
  middleware: [auth(Permission.updateUsersBankAccount)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const userStore = useInjected(userStoreKey)
    const { bankAccount, user } = useInjected(userStateKey)
    const createFormValue = (bankAccount: BankAccount, userId: UserId): Form => ({
      ...bankAccount,
      userId
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('users.bankAccount.edit', user),
      errors,
      progress,
      user,
      value: createFormValue(bankAccount.value!, user.value!.id),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const userId = user.value!.id
          await userStore.updateBankAccount({ form })
          await catchErrorStack(() => $router.replace(`/users/${userId}`))
          $snackbar.success('銀行口座情報を編集しました。')
        }),
        (error: Error) => $alert.error('銀行口座情報の編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '利用者銀行口座情報を編集'
  })
})
</script>
