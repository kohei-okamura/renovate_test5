<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div>
    <z-data-card :title="title">
      <z-data-card-item
        label="請求先"
        :icon="$icons.billing"
        :value="resolveBillingDestination(billingDestination.destination)"
      />
      <z-data-card-item
        label="支払方法"
        :icon="$icons.wallet"
        :value="resolvePaymentMethod(billingDestination.paymentMethod)"
      />
      <z-data-card-item
        v-if="billingDestination.paymentMethod === PaymentMethod.withdrawal"
        label="契約者番号"
        :value="billingDestination.contractNumber"
      />
      <z-data-card-item
        v-if="isCorporation"
        label="法人名・団体名"
        :icon="$icons.office"
        :value="billingDestination.corporationName"
      />
      <template v-if="isAgent || isCorporation">
        <z-data-card-item
          :icon="$icons.personalName"
          :label="isAgent ? '氏名' : '担当者名'"
          :value="billingDestination.agentName"
        />
        <z-data-card-item label="住所" :icon="$icons.addr">
          〒{{ billingDestination.addr.postcode }}<br>
          {{ resolvePrefecture(billingDestination.addr.prefecture) }}{{ billingDestination.addr.city }}
          {{ billingDestination.addr.street }}
          <template v-if="billingDestination.addr.apartment"><br>{{ billingDestination.addr.apartment }}</template>
        </z-data-card-item>
        <z-data-card-item label="電話番号" :icon="$icons.tel" :value="billingDestination.tel" />
      </template>
      <v-card-actions v-if="canChangePaymentMethod">
        <v-spacer />
        <v-btn color="primary" data-payment-method-button text @click="showChangeDialog">支払方法を変更する</v-btn>
      </v-card-actions>
    </z-data-card>
    <z-prompt-dialog
      :active="isDialogActive"
      :options="dialogOptions"
      @click:negative="onClickNegative"
      @click:positive="onClickPositive"
    >
      <template #form>
        <validation-observer ref="observer" tag="div">
          <z-form-card-item-set :icon="$icons.wallet">
            <z-form-card-item
              v-slot="{ errors }"
              data-payment-method-form
              vid="paymentMethod"
              :rules="rules.paymentMethod"
            >
              <v-radio-group v-model="selectedPaymentMethod" :error-messages="errors" dense>
                <z-flex>
                  <div v-for="x in validPaymentMethods" :key="x" class="mr-4">
                    <v-radio :label="resolvePaymentMethod(x)" :value="x" />
                  </div>
                </z-flex>
              </v-radio-group>
            </z-form-card-item>
          </z-form-card-item-set>
        </validation-observer>
      </template>
    </z-prompt-dialog>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from '@nuxtjs/composition-api'
import { BillingDestination, resolveBillingDestination } from '@zinger/enums/lib/billing-destination'
import { PaymentMethod, resolvePaymentMethod } from '@zinger/enums/lib/payment-method'
import { resolvePrefecture } from '@zinger/enums/lib/prefecture'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { useAuth } from '~/composables/use-auth'
import { UserBillingBankAccount } from '~/models/user-billing-bank-account'
import { UserBillingDestination } from '~/models/user-billing-destination'
import { observerRef } from '~/support/reactive'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = Readonly<{
  bankAccount?: UserBillingBankAccount
  billingDestination: UserBillingDestination
  result?: UserBillingResult
  title: string
}>

export default defineComponent<Props>({
  name: 'ZBillingDestinationCard',
  props: {
    bankAccount: { type: Object, default: undefined },
    billingDestination: { type: Object, required: true },
    result: { type: Number, default: undefined },
    title: { type: String, default: '請求先' }
  },
  setup (props, context) {
    const { isAuthorized, permissions } = useAuth()
    const selectedPaymentMethod = ref(props.billingDestination.paymentMethod)
    const canChangePaymentMethod = computed(() => {
      return isAuthorized.value([permissions.updateUserBillings]) &&
        props.result === UserBillingResult.pending
    })
    const isAgent = computed(() => props.billingDestination.destination === BillingDestination.agent)
    const isCorporation = computed(() => props.billingDestination.destination === BillingDestination.corporation)
    /*
     * 支払方法の変更
     */
    const useDialog = () => {
      const observer = observerRef()
      const isDialogActive = ref(false)
      const dialogOptions = {
        message: '支払方法を選択して変更を押してください。',
        positive: '変更',
        width: 480
      }
      const rules = computed(() => {
        const custom = {
          message: '口座振替への変更はできません。',
          validate: () => {
            return props.billingDestination.paymentMethod === PaymentMethod.withdrawal ||
              (selectedPaymentMethod.value !== PaymentMethod.none &&
                selectedPaymentMethod.value !== PaymentMethod.withdrawal)
          }
        }
        return validationRules({
          paymentMethod: { required, custom }
        })
      })
      const showChangeDialog = () => {
        isDialogActive.value = true
      }
      const onClickNegative = (e: Event) => {
        e.stopPropagation()
        observer.value?.reset()
        isDialogActive.value = false
        // キャンセル時はフォームの値を戻す（画面がちらつくので少し待つ）
        setTimeout(() => {
          selectedPaymentMethod.value = props.billingDestination.paymentMethod
        }, 100)
      }
      const onClickPositive = async (e: Event) => {
        e.stopPropagation()
        if (await observer.value?.validate()) {
          if (selectedPaymentMethod.value !== props.billingDestination.paymentMethod) {
            context.emit('click:update', selectedPaymentMethod.value)
          }
          isDialogActive.value = false
        }
      }

      return {
        dialogOptions,
        isDialogActive,
        observer,
        onClickNegative,
        onClickPositive,
        rules,
        showChangeDialog
      }
    }

    return {
      ...useDialog(),
      canChangePaymentMethod,
      isAgent,
      isCorporation,
      PaymentMethod,
      resolveBillingDestination,
      resolvePaymentMethod,
      resolvePrefecture,
      selectedPaymentMethod,
      validPaymentMethods: PaymentMethod.values.filter(x => x !== PaymentMethod.none)
    }
  }
})
</script>
