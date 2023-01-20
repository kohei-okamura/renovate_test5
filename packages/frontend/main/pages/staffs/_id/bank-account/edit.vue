<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-data-card title="基本情報">
      <z-data-card-item label="スタッフ名" :icon="$icons.staff" :value="staff.name.displayName" />
    </z-data-card>
    <z-bank-account-form :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { staffStateKey, staffStoreKey } from '~/composables/stores/use-staff-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { BankAccount } from '~/models/bank-account'
import { StaffId } from '~/models/staff'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'

type Form = Partial<BankAccountsApi.Form>

export default defineComponent({
  name: 'StaffsBankAccountEditPage',
  middleware: [auth(Permission.updateStaffs)],
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
      ...useBreadcrumbs('staffs.bankAccount.edit', staff),
      errors,
      progress,
      staff,
      value: createFormValue(bankAccount.value!, staff.value!.id),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const staffId = staff.value!.id
          await staffStore.updateBankAccount({ form })
          await catchErrorStack(() => $router.replace(`/staffs/${staffId}`))
          $snackbar.success('銀行口座情報を編集しました。')
        }),
        (error: Error) => $alert.error('銀行口座情報の編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: 'スタッフ銀行口座情報を編集'
  })
})
</script>
