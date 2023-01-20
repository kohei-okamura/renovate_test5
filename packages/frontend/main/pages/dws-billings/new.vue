<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-billing-form :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { useAxios } from '~/composables/use-axios'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { DwsBillingsApi } from '~/services/api/dws-billings-api'
import { ValidationObserverInstance } from '~/support/validation/types'

type Form = Partial<DwsBillingsApi.CreateForm>

export default defineComponent({
  name: 'DwsBillingsNewPage',
  middleware: [auth(Permission.createBillings)],
  setup () {
    const { $api, $form } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { execute } = useJobWithNotification()
    // FYI: 値の変更監視は子要素（正確には子要素で使用している useFormBindings）でやっている（分かりにくいので補足）
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('dwsBillings.new'),
      errors,
      progress,
      value: {},
      submit: (form: Form, observer?: ValidationObserverInstance) => withAxios(() => {
        return $form.submit(() => execute({
          notificationProps: {
            text: {
              progress: '障害福祉サービス請求の作成を開始します',
              success: '障害福祉サービス請求の作成に成功しました',
              failure: '障害福祉サービス請求の作成に失敗しました'
            }
          },
          process: () => $api.dwsBillings.create({ form }).then(res => {
            // ジョブが正常に開始されたら変更の監視をリセットする
            observer?.reset()
            return res
          })
        }))
      })
    }
  },
  head: () => ({
    title: '障害福祉サービス請求を作成 '
  })
})
</script>
