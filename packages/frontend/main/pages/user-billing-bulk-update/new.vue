<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <v-alert
      v-if="registrationErrors"
      class="text-caption text-sm-body-2 mb-3"
      data-withdrawal-errors
      dense
      type="error"
    >
      <template v-for="(error, i) in registrationErrors">
        {{ error }}<br :key="i">
      </template>
    </v-alert>
    <z-user-billing-bulk-upload-form v-model="uploadValue" :errors="errors" :progress="progress" @submit="upload" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent, reactive, toRefs } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { billingBulkUpdateStoreKey } from '~/composables/stores/use-billing-bulk-update-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { useJobWithNotification } from '~/composables/use-job-with-notification'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { WithdrawalTransactionsApi } from '~/services/api/withdrawal-transactions-api'
import { ValidationObserverInstance } from '~/support/validation/types'

export default defineComponent({
  name: 'UserBillingBulkUpdateNewPage',
  middleware: [auth(Permission.createWithdrawalTransactions)],
  setup () {
    const { $api, $form } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { execute } = useJobWithNotification()

    const billingBulkUpdateStore = useInjected(billingBulkUpdateStoreKey)
    const billingBulkUpdateState = billingBulkUpdateStore.state

    const data = reactive({
      uploadValue: {
        file: undefined
      }
    })

    // FYI: 値の変更監視は子要素（正確には子要素で使用している useFormBindings）でやっている（分かりにくいので補足）
    $form.preventUnexpectedUnload()

    const upload = (
      form: WithdrawalTransactionsApi.ImportForm,
      observer?: ValidationObserverInstance
    ) => withAxios(() => {
      return $form.submit(() => execute({
        notificationProps: {
          featureName: '全銀ファイルアップロード',
          linkToOnFailure: '/user-billing-bulk-update/new'
        },
        process: () => {
          billingBulkUpdateStore.resetState()
          return $api.withdrawalTransactions.import({ form }).then(res => {
            // ジョブが正常に開始されたら変更の監視をリセットする
            observer?.reset()
            return res
          })
        },
        success: () => {
          data.uploadValue = { file: undefined }
        },
        failure: errors => {
          billingBulkUpdateStore.updateErrors(errors)
        }
      }))
    })

    return {
      ...toRefs(data),
      ...useBreadcrumbs('userBillings.upload'),
      errors,
      progress,
      upload,
      registrationErrors: billingBulkUpdateState.errors
    }
  },
  head: () => ({
    title: '全銀ファイルアップロード'
  })
})
</script>

<style lang="scss" module>
.errorCardWrapper {
  position: relative;
}

.errorCardOverlay {
  background: linear-gradient(rgba(255, 255, 255, 0) 0%, white 56%, white 100%);
  bottom: 0;
  height: 40px;
  position: absolute;
  width: 100%;
}

.errorSeeMore {
  bottom: -20px;
  color: rgba(0, 0, 0, 0.6);
  height: 40px;
  margin: 0 auto;
  left: 0;
  position: absolute;
  right: 0;
  background: linear-gradient(white 32%, rgba(241, 246, 250, 0));
}
</style>
