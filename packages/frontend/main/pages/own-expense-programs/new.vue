<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-own-expense-program-form
      button-text="登録"
      :errors="errors"
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
import { useAxios } from '~/composables/use-axios'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'

type Form = OwnExpenseProgramsApi.Form

export default defineComponent({
  name: 'OwnExpenseProgramsNewPage',
  middleware: [auth(Permission.createOwnExpensePrograms)],
  setup () {
    const { $alert, $api, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('ownExpensePrograms.new'),
      errors,
      permission: Permission.createOwnExpensePrograms,
      progress,
      value: {},
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await $api.ownExpensePrograms.create({ form })
          await catchErrorStack(() => $router.replace('/own-expense-programs?restore=1'))
          $snackbar.success('自費サービス情報を登録しました。')
        }),
        (error: Error) => $alert.error('自費サービス情報の登録に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '自費サービスを登録'
  })
})
</script>
