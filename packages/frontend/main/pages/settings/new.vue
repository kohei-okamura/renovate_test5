<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-setting-form button-text="登録" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { settingStoreKey } from '~/composables/stores/use-setting-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { SettingApi } from '~/services/api/setting-api'

type Form = Partial<SettingApi.Form>

export default defineComponent({
  name: 'SettingsNewPage',
  middleware: [auth(Permission.createOrganizationSettings)],
  setup () {
    const { $form, $alert, $snackbar, $router } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const settingStore = useInjected(settingStoreKey)
    return {
      ...useBreadcrumbs('setting.new'),
      errors,
      progress,
      value: {},
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await settingStore.create({ form })
          await catchErrorStack(async () => {
            await settingStore.get()
            await $router.replace('/settings')
          })
          $snackbar.success('事業者別設定を登録しました。')
        }),
        (error: Error) => $alert.error('事業者別設定の登録に失敗しました。', error.stack)
      )
    }
  }
})
</script>
