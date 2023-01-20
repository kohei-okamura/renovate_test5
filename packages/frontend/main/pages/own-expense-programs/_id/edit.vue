<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-own-expense-program-form
      button-text="保存"
      :errors="errors"
      :is-edit="true"
      :permission="permission"
      :progress="progress"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import {
  ownExpenseProgramStateKey,
  ownExpenseProgramStoreKey
} from '~/composables/stores/use-own-expense-program-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { OwnExpenseProgram } from '~/models/own-expense-program'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'

type Form = OwnExpenseProgramsApi.Form

export default defineComponent({
  name: 'OwnExpenseProgramsEditPage',
  middleware: [auth(Permission.updateOwnExpensePrograms)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { ownExpenseProgram } = useInjected(ownExpenseProgramStateKey)
    const ownExpenseProgramStore = useInjected(ownExpenseProgramStoreKey)
    const createFormValue = (x: OwnExpenseProgram): Form => ({
      officeId: x.officeId,
      name: x.name,
      durationMinutes: x.durationMinutes,
      fee: x.fee,
      note: x.note
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('ownExpensePrograms.edit', ownExpenseProgram),
      errors,
      permission: Permission.updateOwnExpensePrograms,
      progress,
      value: createFormValue(ownExpenseProgram.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = ownExpenseProgram.value!.id
          await ownExpenseProgramStore.update({ form, id })
          await catchErrorStack(() => $router.replace(`/own-expense-programs/${id}`))
          $snackbar.success('自費サービスを編集しました。')
        }),
        (error: Error) => $alert.error('自費サービスの編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '自費サービスを編集'
  })
})
</script>
