<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card :title="title">
        <z-form-card-item-set :icon="$icons.bank">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-bank-name vid="bankName" :rules="rules.bankName">
                <z-text-field
                  v-model.trim="form.bankName"
                  data-bank-name-input
                  label="銀行名 *"
                  :error-messages="errors"
                />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-bank-code vid="bankCode" :rules="rules.bankCode">
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
                vid="bankBranchName"
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
                vid="bankBranchCode"
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
            vid="bankAccountType"
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
            vid="bankAccountHolder"
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
            vid="bankAccountNumber"
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
      </z-form-card>
      <z-form-action-button text="保存" :disabled="progress" :icon="$icons.save" :loading="progress" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { BankAccountType } from '@zinger/enums/lib/bank-account-type'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useBankAccountNumberHint } from '~/composables/use-bank-account-number-hint'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { autoKana } from '~/directives/auto-kana'
import { BankAccountsApi } from '~/services/api/bank-accounts-api'
import { numeric, required, zenginDataRecordChar } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<BankAccountsApi.Form>

export default defineComponent<Props>({
  name: 'ZBankAccountForm',
  directives: {
    autoKana
  },
  props: {
    ...getFormPropsOptions(),
    title: { type: String, default: '銀行口座情報' }
  },
  setup: (props, context) => {
    const { form, observer, submit } = useFormBindings(props, context)
    const isJapanPostBank = computed(() => form.bankCode === '9900')
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
    return {
      bankAccountNumberMessage: useBankAccountNumberHint(isJapanPostBank),
      bankAccountTypes: enumerableOptions(BankAccountType),
      form,
      observer,
      submit,
      rules
    }
  }
})
</script>

<style lang="scss" module>
.multiLine {
  white-space: pre-line;
}
</style>
