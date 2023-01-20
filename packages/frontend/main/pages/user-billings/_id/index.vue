<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <v-alert
      v-if="hasError"
      class="text-sm-body-2 mb-3"
      data-action-errors
      dense
      type="error"
    >
      <template v-for="x in errors">
        {{ x }}<br :key="x">
      </template>
    </v-alert>
    <z-data-card title="事業所">
      <z-data-card-item label="事業所名" :icon="$icons.office">
        <nuxt-link
          v-if="isAuthorized([permissions.viewInternalOffices, permissions.viewExternalOffices])"
          :to="`/offices/${userBilling.officeId}`"
        >
          {{ userBilling.office.name }}
        </nuxt-link>
        <template v-else>{{ userBilling.office.name }}</template>
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="利用者">
      <z-data-card-item label="氏名" :icon="$icons.user">
        <nuxt-link v-if="isAuthorized([permissions.viewUsers])" :to="`/users/${userBilling.userId}`">
          {{ userBilling.user.name.displayName }}
        </nuxt-link>
        <template v-else>{{ userBilling.user.name.displayName }}</template>
      </z-data-card-item>
    </z-data-card>
    <z-billing-destination-card
      :billing-destination="userBilling.user.billingDestination"
      :result="userBilling.result"
      @click:update="updatePaymentMethod"
    />
    <z-bank-account-card
      v-if="isWithdrawal"
      v-bind="bankAccount"
    >
      <v-btn
        v-if="canChangeBankAccountInformation"
        color="primary"
        data-bank-account-information-button
        text
        @click="showChangeDialog"
      >
        銀行口座情報を変更する
      </v-btn>
    </z-bank-account-card>
    <z-bank-account-form-dialog
      :value="bankAccount"
      :dialog="isDialogActive"
      :errors="axiosErrors"
      :progress="progress"
      @update:dialog="updateDialog"
      @submit="updateBankAccount"
    />
    <z-user-billing-information-card :user-billing="userBilling" />
    <z-user-billing-item-card :user-billing="userBilling" />
    <z-user-billing-item-detail-card
      :user-billing="userBilling"
      @click:update="updateCarriedOverAmount"
    />
    <z-system-meta-card :id="userBilling.id" :created-at="userBilling.createdAt" :updated-at="userBilling.updatedAt" />
    <z-fab-speed-dial
      data-fab
      :icon="$icons.download"
    >
      <z-fab-speed-dial-button
        v-if="userBilling.result !== UserBillingResult.none"
        data-download-button="invoice"
        :icon="$icons.billing"
        @click="downloadInvoices"
      >
        請求書をダウンロード
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="userBilling.result === UserBillingResult.paid"
        data-download-button="receipt"
        :icon="$icons.wallet"
        @click="downloadReceipts"
      >
        領収書をダウンロード
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="userBilling.dwsItem !== undefined"
        data-download-button="notice"
        :icon="$icons.notification"
        @click="downloadNotices"
      >
        代理受領額通知書をダウンロード
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        v-if="!(userBilling.dwsItem === undefined && userBilling.ltcsItem === undefined)"
        data-download-button="statement"
        :icon="$icons.user"
        @click="downloadStatements"
      >
        介護サービス利用明細書をダウンロード
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
    <z-progress :value="isShowProgress && progress" />
    <z-date-confirm-dialog
      data-date-confirm-dialog="download"
      message="印字する発行日を選択してください"
      positive-label="ダウンロード"
      :active="fileDownloadDialog.isActive.value"
      @click:negative="fileDownloadDialog.cancel"
      @click:positive="fileDownloadDialog.run"
    />
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent, ref, UnwrapRef } from '@nuxtjs/composition-api'
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { PaymentMethod } from '@zinger/enums/lib/payment-method'
import { Permission } from '@zinger/enums/lib/permission'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import deepmerge from 'deepmerge'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { userBillingStateKey, userBillingStoreKey } from '~/composables/stores/use-user-billing-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUserBillingFileDownloader } from '~/composables/use-user-billing-file-downloader'
import { auth } from '~/middleware/auth'
import { DateLike } from '~/models/date'
import { UserBillingBankAccount } from '~/models/user-billing-bank-account'
import { UserBillingsApi } from '~/services/api/user-billings-api'

type Form = UserBillingsApi.UpdateForm

const emptyUserBillingBankAccount: UserBillingBankAccount = {
  bankName: ' ',
  bankCode: ' ',
  bankBranchName: ' ',
  bankBranchCode: ' ',
  bankAccountType: BankAccountType.unknown,
  bankAccountNumber: ' ',
  bankAccountHolder: ' '
}

export default defineComponent({
  name: 'UserBillingsViewPage',
  middleware: [auth(Permission.viewUserBillings)],
  setup () {
    const isShowProgress = ref(true)
    const isSuccess = ref(false)
    const { $snackbar } = usePlugins()
    const { userBilling } = useInjected(userBillingStateKey)
    const store = useInjected(userBillingStoreKey)
    const isWithdrawal = computed(() => {
      return userBilling.value?.user.billingDestination.paymentMethod === PaymentMethod.withdrawal
    })
    const { isAuthorized, permissions } = useAuth()
    const canChangeBankAccountInformation = computed(() => {
      return isAuthorized.value([permissions.updateUserBillings]) &&
        userBilling.value?.result === UserBillingResult.pending
    })
    const { errors: axiosErrors, progress, withAxios } = useAxios()
    const submit = (name: string, form: Form) => {
      return withAxios(
        async () => {
          isSuccess.value = false
          await store.update({ form, id: userBilling.value!.id })
          isSuccess.value = true
          $snackbar.success(`${name}を変更しました。`)
        },
        () => {
          $snackbar.error(`${name}の変更に失敗しました。`)
        }
      )
    }
    const updateCarriedOverAmount = (carriedOverAmount: number) => {
      isShowProgress.value = true
      const form = {
        carriedOverAmount,
        paymentMethod: userBilling.value?.user.billingDestination.paymentMethod ?? PaymentMethod.none,
        bankAccount: userBilling.value?.user.bankAccount ?? emptyUserBillingBankAccount
      }
      submit('繰越金額', form)
    }
    const updatePaymentMethod = (paymentMethod: PaymentMethod) => {
      isShowProgress.value = true
      const form = {
        carriedOverAmount: userBilling.value?.carriedOverAmount ?? 0,
        paymentMethod,
        bankAccount: userBilling.value?.user.bankAccount ?? emptyUserBillingBankAccount
      }
      submit('支払方法', form)
    }
    const useBankAccount = () => {
      const isDialogActive = ref(false)
      const showChangeDialog = () => {
        isDialogActive.value = true
      }
      const updateDialog = (dialog: boolean) => {
        isDialogActive.value = dialog
      }
      const updateBankAccount = async (bankAccount: UserBillingBankAccount) => {
        isShowProgress.value = true
        const form = {
          carriedOverAmount: userBilling.value?.carriedOverAmount ?? 0,
          paymentMethod: userBilling.value?.user.billingDestination.paymentMethod ?? PaymentMethod.none,
          bankAccount
        }
        await submit('銀行口座情報', form)
        if (isSuccess.value) {
          isDialogActive.value = false
        }
      }
      return { isDialogActive, showChangeDialog, updateDialog, updateBankAccount }
    }

    // 帳票ダウンロード
    const downloader = useUserBillingFileDownloader()
    const fileDownloadDialog = (() => {
      const active = ref(false)
      const isActive = computed(() => active.value)
      const fn = ref<((form: UserBillingsApi.DownloadForm) => Promise<void>) | undefined>(undefined)
      const retain = (func: NonNullable<UnwrapRef<typeof fn>>) => {
        fn.value = func
        active.value = true
      }
      const cancel = () => { active.value = false }
      const run = (date: DateLike) => {
        if (!fn.value) {
          throw new Error('IllegalStateException')
        }
        active.value = false
        return fn.value({ ids: [userBilling.value!.id], issuedOn: date })
      }
      return { isActive, cancel, retain, run }
    })()
    const errors = computed(() => Object.values(deepmerge(axiosErrors, downloader.errors).value).flat())
    const hasError = computed(() => Object.keys(errors.value).length >= 1)
    const clearErrors = () => {
      axiosErrors.value = {}
      downloader.errors.value = {}
    }

    return {
      ...useBankAccount(),
      ...useBreadcrumbs('userBillings.view', userBilling),
      ...useAuth(),
      axiosErrors,
      canChangeBankAccountInformation,
      clearErrors,
      downloadInvoices: () => fileDownloadDialog.retain(downloader.downloadInvoices),
      downloadNotices: () => fileDownloadDialog.retain(downloader.downloadNotices),
      downloadReceipts: () => fileDownloadDialog.retain(downloader.downloadReceipts),
      downloadStatements: () => fileDownloadDialog.retain(downloader.downloadStatements),
      bankAccount: computed(() => userBilling.value?.user.bankAccount),
      errors,
      fileDownloadDialog,
      hasError,
      isShowProgress,
      isWithdrawal,
      progress,
      updateCarriedOverAmount,
      updatePaymentMethod,
      userBilling,
      UserBillingResult
    }
  },
  head: () => ({
    title: '利用者請求編集'
  })
})
</script>
