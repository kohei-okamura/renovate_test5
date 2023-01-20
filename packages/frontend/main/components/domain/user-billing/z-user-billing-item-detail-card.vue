<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div>
    <z-data-card class="mt-2">
      <z-data-card-item label="税抜金額(10%)" :value="numeralWithYen(amounts.normalRateWithoutTax)" :icon="$icons.yen" />
      <z-data-card-item label="消費税額(10%)" :value="numeralWithYen(amounts.normalRateTax)" />
      <z-data-card-item label="税抜金額(8%)" :value="numeralWithYen(amounts.reducedRateWithoutTax)" />
      <z-data-card-item label="消費税額(8%)" :value="numeralWithYen(amounts.reducedRateTax)" />
      <z-data-card-item label="繰越金額" :value="currentCarriedOverAmount" />
      <z-data-card-item label="合計" :value="totalAmount" />
      <z-data-card-item label="医療費控除対象額" :value="numeralWithYen(amounts.medicalDeductionAmount)" />
      <v-card-actions v-if="canChangeCarriedOverAmount">
        <v-spacer />
        <v-btn color="primary" data-carried-over-amount-button text @click="showChangeDialog">繰越金額を変更する</v-btn>
      </v-card-actions>
    </z-data-card>
    <z-prompt-dialog
      data-carried-over-amount-dialog
      :active="isDialogActive"
      :options="dialogOptions"
      @click:negative="onClickNegative"
      @click:positive="onClickPositive"
    >
      <template #form>
        <validation-observer ref="observer" tag="div">
          <z-data-card-item label="合計" :icon="$icons.yen" :value="totalAmount" />
          <z-data-card-item label="現在の繰越金額" :value="currentCarriedOverAmount" />
          <z-form-card-item-set>
            <z-form-card-item
              v-slot="{ errors }"
              data-carried-over-amount-form
              vid="carriedOverAmount"
              :rules="rules.carriedOverAmount"
            >
              <z-text-field
                v-model="changedCarriedOverAmount"
                class="z-text-field--numeric"
                label="変更後の繰越金額 *"
                suffix="円"
                :class="$style.textField"
                :error-messages="errors"
              />
            </z-form-card-item>
          </z-form-card-item-set>
        </validation-observer>
      </template>
    </z-prompt-dialog>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, ref } from '@nuxtjs/composition-api'
import { ConsumptionTaxRate } from '@zinger/enums/lib/consumption-tax-rate'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { numeral } from '~/composables/numeral'
import { useAuth } from '~/composables/use-auth'
import { UserBilling } from '~/models/user-billing'
import { observerRef } from '~/support/reactive'
import { integer, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = Readonly<{
  userBilling: UserBilling
}>

export default defineComponent<Props>({
  name: 'ZUserBillingItemDetailCard',
  props: {
    userBilling: { type: Object, required: true }
  },
  setup (props, context) {
    const { isAuthorized, permissions } = useAuth()
    const numeralWithYen = (x: number) => numeral(x) + '円'
    const amounts = computed(() => {
      return [
        props.userBilling.dwsItem,
        props.userBilling.ltcsItem,
        ...props.userBilling.otherItems ?? []
      ].reduce((acc, cur) => {
        if (!cur) {
          return acc
        }
        return {
          ...acc,
          ...(((ac, cu) => {
            if (cu.tax === ConsumptionTaxRate.ten) {
              return {
                normalRateWithoutTax: ac.normalRateWithoutTax + cu.copayWithoutTax,
                normalRateTax: ac.normalRateTax + (cu.copayWithTax - cu.copayWithoutTax)
              }
            } else if (cu.tax === ConsumptionTaxRate.eight) {
              return {
                reducedRateWithoutTax: ac.reducedRateWithoutTax + cu.copayWithoutTax,
                reducedRateTax: ac.reducedRateTax + (cu.copayWithTax - cu.copayWithoutTax)
              }
            }
          })(acc, cur)),
          medicalDeductionAmount: acc.medicalDeductionAmount + cur.medicalDeductionAmount
        }
      }, {
        medicalDeductionAmount: 0,
        normalRateWithoutTax: 0,
        normalRateTax: 0,
        reducedRateWithoutTax: 0,
        reducedRateTax: 0
      })
    })
    const currentCarriedOverAmount = computed(() => props.userBilling.carriedOverAmount)
    const changedCarriedOverAmount = ref(props.userBilling.carriedOverAmount)
    // 型だと changedCarriedOverAmount は number なのでエラーを回避するために文字列リテラルを使っている
    const parsedCarriedOverAmount = computed(() => parseInt(`${changedCarriedOverAmount.value ?? 0}`))
    const canChangeCarriedOverAmount = computed(() => {
      return isAuthorized.value([permissions.updateUserBillings]) &&
        (
          props.userBilling?.result === UserBillingResult.pending ||
          props.userBilling?.result === UserBillingResult.none
        )
    })

    /*
     * 繰越金額の変更
     */
    const useDialog = () => {
      const observer = observerRef()
      const isDialogActive = ref(false)
      const isSameCarriedOverAmount = computed(() => parsedCarriedOverAmount.value === currentCarriedOverAmount.value)
      const dialogOptions = {
        message: '繰越金額を入力して実行を押してください。',
        positive: '保存',
        width: 550
      }
      const rules = computed(() => {
        const custom = {
          message: isSameCarriedOverAmount.value
            ? '繰越金額を変更してください。'
            : '繰越金額と各明細の合計金額の合計が0円以上になるようにしてください。',
          validate: () => {
            // 入力値は文字列で入ってくるので数字に変換してから加算する
            // v-model.number を使う方法もあるが、それだと誤入力が通過する可能性があるため不採用
            // e.g. '12345a6789' が 12345 と解釈される
            const diff = -(currentCarriedOverAmount.value ?? 0) + parsedCarriedOverAmount.value
            return props.userBilling.totalAmount + diff >= 0 && !isSameCarriedOverAmount.value
          }
        }
        return validationRules({
          carriedOverAmount: { required, integer, custom }
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
          changedCarriedOverAmount.value = currentCarriedOverAmount.value
        }, 100)
      }
      const onClickPositive = async (e: Event) => {
        e.stopPropagation()
        if (await observer.value?.validate()) {
          context.emit('click:update', parsedCarriedOverAmount.value)
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
      amounts,
      canChangeCarriedOverAmount,
      changedCarriedOverAmount,
      currentCarriedOverAmount: computed(() => numeralWithYen(currentCarriedOverAmount.value)),
      numeralWithYen,
      totalAmount: computed(() => numeralWithYen(props.userBilling.totalAmount))
    }
  }
})
</script>

<style lang="scss" module>
.textField {
  :global(.v-input__slot) {
    width: 60% !important;
  }
}
</style>
