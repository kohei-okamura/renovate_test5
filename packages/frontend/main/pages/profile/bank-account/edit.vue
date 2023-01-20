<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-bank-account-form title="給与振込口座" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { staffStateKey, staffStoreKey } from '~/composables/stores/use-staff-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { BankAccount } from '~/models/bank-account'
import { StaffId } from '~/models/staff'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'

type Form = Partial<BankAccountsApi.Form>

export default defineComponent({
  name: 'SettingsBankAccountEditPage',
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const staffStore = useInjected(staffStoreKey)
    const { bankAccount, staff } = useInjected(staffStateKey)
    const createFormValue = (bankAccount: BankAccount, staffId: StaffId): Form => ({
      ...bankAccount,
      staffId
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('settings.profile.bankAccount.edit'),
      errors,
      progress,
      staff,
      value: createFormValue(bankAccount.value!, staff.value!.id),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await staffStore.updateBankAccount({ form })
          await catchErrorStack(() => $router.replace('/profile'))
          $snackbar.success('給与振込口座を登録しました。')
        }),
        (error: Error) => $alert.error('給与振込口座の登録に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: 'スタッフ給与振込口座編集'
  })
})
</script>
