<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-setting-form button-text="保存" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { settingStateKey, settingStoreKey } from '~/composables/stores/use-setting-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { Setting } from '~/models/setting'
import { SettingApi } from '~/services/api/setting-api'

type Form = Partial<SettingApi.Form>

export default defineComponent({
  name: 'SettingsEditPage',
  middleware: [auth(Permission.updateOrganizationSettings)],
  setup () {
    const { $form, $alert, $snackbar, $router } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const settingStore = useInjected(settingStoreKey)
    const { organizationSetting } = useInjected(settingStateKey)
    const createFormValue = (x: Setting): Form => ({
      bankingClientCode: x.bankingClientCode
    })
    return {
      ...useBreadcrumbs('setting.edit'),
      errors,
      progress,
      value: createFormValue(organizationSetting.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          await settingStore.update({ form })
          await catchErrorStack(() => $router.replace('/settings'))
          $snackbar.success('事業者別設定を更新しました。')
        }),
        (error: Error) => $alert.error('事業者別設定の更新に失敗しました。', error.stack)
      )
    }
  }
})
</script>
