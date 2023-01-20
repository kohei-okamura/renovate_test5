<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div>
    <z-data-card title="請求情報">
      <z-data-card-item label="請求結果" :icon="$icons.billing" :value="resolveUserBillingResult(userBilling.result)" />
      <z-data-card-item
        v-if="isWithdrawal"
        label="振替結果"
        :value="resolveWithdrawalResultCode(userBilling.withdrawalResultCode)"
      />
      <z-data-card-item label="合計金額" :icon="$icons.yen" :value="`${numeral(userBilling.totalAmount)}円`" />
      <z-data-card-item label="発行日" :icon="$icons.date">
        <z-era-date :value="userBilling.issuedOn" />
      </z-data-card-item>
      <z-data-card-item label="サービス提供年月" :icon="$icons.month">
        {{ eraMonth(userBilling.providedIn) }}
      </z-data-card-item>
      <z-data-card-item v-if="isWithdrawal" label="口座振替日" :icon="$icons.date">
        <z-era-date :value="userBilling.deductedOn" />
      </z-data-card-item>
      <z-data-card-item v-else label="お支払期限日" :icon="$icons.date">
        <z-era-date :value="userBilling.dueDate" />
      </z-data-card-item>
      <z-data-card-item v-if="isUserBillingResultPaid" label="入金日" :icon="$icons.date">
        <z-era-date :value="userBilling.depositedAt" />
      </z-data-card-item>
      <v-card-actions v-if="canUpdateDeposit">
        <v-spacer />
        <v-btn v-if="isUserBillingResultPending" color="primary" text @click="registration">入金日を登録する</v-btn>
        <v-btn v-if="isUserBillingResultPaid" color="primary" text @click="cancellation">入金日を削除する</v-btn>
      </v-card-actions>
    </z-data-card>
    <z-progress :value="axiosProgress" />
    <z-date-confirm-dialog
      message="入金日を選択してください"
      positive-label="登録"
      :active="dateRegistrationDialog.active.value"
      :in-progress="axiosProgress"
      :max="today"
      @click:negative="dateRegistrationDialog.cancel"
      @click:positive="dateRegistrationDialog.run"
    />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from '@nuxtjs/composition-api'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { resolveUserBillingResult, UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { resolveWithdrawalResultCode } from '@zinger/enums/lib/withdrawal-result-code'
import { colors } from '~/colors'
import { eraMonth } from '~/composables/era-date'
import { numeral } from '~/composables/numeral'
import { userBillingStoreKey } from '~/composables/stores/use-user-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { useJobPolling } from '~/composables/use-job-polling'
import { usePlugins } from '~/composables/use-plugins'
import { DateLike } from '~/models/date'
import { UserBilling } from '~/models/user-billing'
import { UserBillingsApi } from '~/services/api/user-billings-api'
import { ConfirmDialogParams } from '~/services/confirm-dialog-service'
import { $datetime } from '~/services/datetime-service'

type Props = Readonly<{
  userBilling: UserBilling
}>

export default defineComponent<Props>({
  name: 'UserBillingInformation',
  props: {
    userBilling: { type: Object, required: true }
  },
  setup (props) {
    const { isAuthorized, permissions } = useAuth()
    const store = useInjected(userBillingStoreKey)
    const useAction = () => {
      const { $api, $confirm, $snackbar } = usePlugins()
      const { errors: axiosError, progress: axiosProgress, withAxios } = useAxios()
      const { startJobPolling } = useJobPolling()
      const runAction = (actionText: string, callback: () => Promise<UserBillingsApi.BatchResponse>) => {
        return withAxios(async () => {
          const result = await startJobPolling(async () => {
            return await callback()
          })
          if (result === false) {
            $snackbar.error(`入金日の${actionText}に失敗しました。`)
          } else {
            const { job } = result
            if (job.status === JobStatus.failure) {
              $snackbar.error(`入金日の${actionText}に失敗しました。`)
            } else if (job.status === JobStatus.success) {
              store.get({ id: props.userBilling.id }).then(
                () => $snackbar.success(`入金日を${actionText}しました。`),
                () => $snackbar.warning('画面情報を更新できませんでした。最新の情報を見るにはブラウザをリロードしてください。')
              )
            }
          }
        })
      }
      const showDialog = ref(false)
      const dateRegistrationDialog = {
        active: showDialog,
        cancel: () => { showDialog.value = false },
        run: async (date: DateLike) => {
          showDialog.value = false
          const ids = [props.userBilling.id]
          const form: UserBillingsApi.DepositRegistrationForm = { ids, depositedOn: date }
          await runAction('登録', () => $api.userBillings.depositRegistration({ form }))
        }
      }
      const registration = () => {
        showDialog.value = true
      }
      const cancellation = async () => {
        const form: UserBillingsApi.DepositCancellationForm = { ids: [props.userBilling.id] }
        const params: ConfirmDialogParams = {
          color: colors.critical,
          message: '入金日を削除します。\n\n本当によろしいですか？',
          positive: '削除'
        }
        if (await $confirm.show(params)) {
          await runAction('削除', () => $api.userBillings.depositCancellation({ form }))
        }
      }
      return { axiosError, axiosProgress, cancellation, dateRegistrationDialog, registration }
    }
    const isUserBillingResultPaid = computed(() => props.userBilling.result === UserBillingResult.paid)
    const isUserBillingResultPending = computed(() => props.userBilling.result === UserBillingResult.pending)
    const isWithdrawal = computed(() => {
      return props.userBilling.user.billingDestination.paymentMethod === PaymentMethod.withdrawal
    })
    const canUpdateDeposit = computed(() => {
      return isAuthorized.value([permissions.updateUserBillings]) && !isWithdrawal.value
    })
    return {
      ...useAction(),
      canUpdateDeposit,
      eraMonth,
      isUserBillingResultPaid,
      isUserBillingResultPending,
      isWithdrawal,
      numeral,
      resolveUserBillingResult,
      resolveWithdrawalResultCode,
      today: $datetime.now
    }
  }
})
</script>
