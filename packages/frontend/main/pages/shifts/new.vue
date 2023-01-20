<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-shift-form
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
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { useAxios } from '~/composables/use-axios'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { ShiftsApi } from '~/services/api/shifts-api'

type Form = Partial<ShiftsApi.Form>

export default defineComponent({
  name: 'ShiftsNewPage',
  middleware: [auth(Permission.createShifts)],
  setup () {
    const { $alert, $api, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('shifts.new'),
      errors,
      permission: Permission.createShifts,
      progress,
      value: {
        options: [ServiceOption.notificationEnabled]
      },
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await $api.shifts.create({ form })
          await catchErrorStack(() => $router.replace('/shifts?restore=1'))
          $snackbar.success('勤務シフト情報を登録しました。')
        }),
        (error: Error) => $alert.error('勤務シフト情報の登録に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '勤務シフトを登録'
  })
})
</script>
