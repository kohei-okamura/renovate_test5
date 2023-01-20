<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-office-form
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
import { OfficesApi } from '~/services/api/offices-api'

type Form = Partial<OfficesApi.Form>

export default defineComponent({
  name: 'OfficesNewPage',
  middleware: [auth(Permission.createInternalOffices, Permission.createExternalOffices)],
  setup () {
    const { $alert, $api, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('offices.new'),
      errors,
      permission: Permission.createInternalOffices,
      progress,
      value: {},
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await $api.offices.create({ form })
          await catchErrorStack(() => $router.replace('/offices?restore=1'))
          $snackbar.success('事業所情報を登録しました。')
        }),
        (error: Error) => $alert.error('事業所情報の登録に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '事業所基本情報を登録'
  })
})
</script>
