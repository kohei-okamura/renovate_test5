<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog persistent transition="dialog" width="640" :value="isActive">
    <v-card>
      <z-card-titlebar color="blue-grey" data-title>
        銀行口座情報を編集
      </z-card-titlebar>
      <validation-observer ref="observer" tag="div" class="pt-6">
        <z-form-card-item-set :icon="$icons.bank">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-bank-name vid="bankAccount.bankName" :rules="rules.bankName">
                <z-text-field
                  v-model.trim="form.bankName"
                  data-bank-name-input
                  label="銀行名 *"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-bank-code vid="bankAccount.bankCode" :rules="rules.bankCode">
                <z-text-field
                  v-model.trim="form.bankCode"
                  data-bank-code-input
                  label="銀行コード *"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item
                v-slot="{ errors }"
                data-bank-branch-name
                vid="bankAccount.bankBranchName"
                :rules="rules.bankBranchName"
              >
                <z-text-field
                  v-model.trim="form.bankBranchName"
                  data-bank-branch-name-input
                  label="支店名 *"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item
                v-slot="{ errors }"
                data-bank-branch-code
                vid="bankAccount.bankBranchCode"
                :rules="rules.bankBranchCode"
              >
                <z-text-field
                  v-model.trim="form.bankBranchCode"
                  data-bank-branch-code-input
                  label="支店コード *"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
          </v-row>
          <z-form-card-item
            v-slot="{ errors }"
            data-bank-account-type
            vid="bankAccount.bankAccountType"
            :rules="rules.bankAccountType"
          >
            <z-select
              v-model="form.bankAccountType"
              label="銀行口座種別 *"
              :error-messages="errors"
              :items="bankAccountTypes"
            />
          </z-form-card-item>
          <z-form-card-item
            v-slot="{ errors }"
            data-bank-account-holder
            vid="bankAccount.bankAccountHolder"
            :rules="rules.bankAccountHolder"
          >
            <z-text-field
              v-model.trim="form.bankAccountHolder"
              v-auto-kana
              data-bank-account-holder-input
              hint="姓と名の間には空白（スペース）を入れてください。例：ツチヤ ハナコ"
              label="口座名義 *"
              persistent-hint
              :error-messages="errors"
            />
          </z-form-card-item>
          <z-form-card-item
            v-slot="{ errors }"
            data-bank-account-number
            vid="bankAccount.bankAccountNumber"
            :rules="rules.bankAccountNumber"
          >
            <z-text-field
              v-model.trim="form.bankAccountNumber"
              data-bank-account-number-input
              label="口座番号 *"
              :error-messages="errors"
              maxlength="8"
            />
            <v-messages :class="[$style.multiLine, 'mx-3 mt-n1']" :value="[bankAccountNumberMessage]" />
          </z-form-card-item>
        </z-form-card-item-set>
      </validation-observer>
      <v-card-actions class="pb-6">
        <v-row justify="center" justify-md="end" no-gutters>
          <v-col cols="5" md="3">
            <v-btn data-cancel text width="100%" @click.stop="onClickNegative">キャンセル</v-btn>
          </v-col>
          <v-col class="pl-4" cols="5" md="3">
            <v-btn color="primary" data-ok depressed width="100%" @click.stop="onClickPositive">
              保存
            </v-btn>
          </v-col>
        </v-row>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { assign } from '@zinger/helpers'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useBankAccountNumberHint } from '~/composables/use-bank-account-number-hint'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { autoKana } from '~/directives/auto-kana'
import { UserBillingBankAccount } from '~/models/user-billing-bank-account'
import { observerRef } from '~/support/reactive'
import { numeric, required, zenginDataRecordChar } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<UserBillingBankAccount> & Readonly<{
  dialog: boolean
}>

export default defineComponent<Props>({
  name: 'ZBankAccountFormDialog',
  directives: {
    autoKana
  },
  props: {
    ...getFormPropsOptions(),
    dialog: { type: Boolean, required: true }
  },
  setup (props, context) {
    const { form, observer, submit } = useFormBindings(props, context)
    const isJapanPostBank = computed(() => form.bankCode === '9900')
    const useDialog = () => {
      const observer = observerRef()
      const isActive = useSyncedProp('dialog', props, context)
      const rules = computed(() => {
        const custom = {
          message: '末尾に1を入力してください。',
          validate: () => !isJapanPostBank.value || !!form.bankAccountNumber?.endsWith('1')
        }
        return validationRules({
          bankName: { required, max: 100 },
          bankCode: { required, digits: 4 },
          bankBranchName: { required, max: 100 },
          bankBranchCode: { required, digits: 3 },
          bankAccountType: { required },
          bankAccountNumber: { required, length: isJapanPostBank.value ? 8 : 7, numeric, custom },
          bankAccountHolder: { required, max: 200, zenginDataRecordChar }
        })
      })
      const showChangeDialog = () => {
        isActive.value = true
      }
      const onClickNegative = () => {
        observer.value?.reset()
        isActive.value = false
        // キャンセル時はフォームの値を戻す（画面がちらつくので少し待つ）
        setTimeout(() => {
          assign(form, props.value)
        }, 100)
      }
      const onClickPositive = async () => {
        await submit()
      }

      return {
        isActive,
        onClickNegative,
        onClickPositive,
        rules,
        showChangeDialog
      }
    }
    return {
      ...useDialog(),
      bankAccountNumberMessage: useBankAccountNumberHint(isJapanPostBank),
      bankAccountTypes: enumerableOptions(BankAccountType),
      form,
      observer
    }
  }
})
</script>

<style lang="scss" module>
.multiLine {
  white-space: pre-line;
}
</style>
