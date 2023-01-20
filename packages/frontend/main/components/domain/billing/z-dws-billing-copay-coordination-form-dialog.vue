<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog v-model="isActive" persistent transition="dialog" width="500">
    <v-form data-form @submit.prevent="submit">
      <v-card>
        <z-card-titlebar color="blue-grey">利用者負担上限額管理結果を{{ actionText }}</z-card-titlebar>
        <v-card-text>
          <div class="subtitle-1 mb-3">上限額管理結果を選択してください。</div>
          <validation-observer ref="observer" tag="div">
            <z-form-card-item
              v-slot="{ errors }"
              data-result
              vid="result"
              :rules="rules.result"
            >
              <v-radio-group
                v-model="form.result"
                :error-messages="errors"
                @change="onChangeResult"
              >
                <v-row v-for="x in resultTypes" :key="x.value">
                  <v-col>
                    <v-radio :label="x.text" :value="x.value" />
                  </v-col>
                </v-row>
              </v-radio-group>
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              class="pl-8"
              data-amount
              vid="amount"
              :rules="rules.amount"
            >
              <z-text-field
                v-model.trim="form.amount"
                label="上限管理後利用者負担額 *"
                suffix="円"
                type="number"
                :disabled="isAppropriated"
                :error-messages="errors"
                :min="0"
              />
            </z-form-card-item>
          </validation-observer>
        </v-card-text>
        <v-card-actions class="pb-4 pt-0 px-4">
          <v-spacer />
          <v-btn data-cancel text :disabled="progress" @click.stop="close">キャンセル</v-btn>
          <v-btn
            color="primary"
            data-ok
            depressed
            type="submit"
            :disabled="progress"
            :loading="progress"
          >
            {{ actionText }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { DwsBillingStatementsApi } from '~/services/api/dws-billing-statements-api'
import { numeric, required } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DwsBillingStatementsApi.UpdateCopayCoordinationForm> & Readonly<{
  dialog: boolean
  amount: number
}>

export default defineComponent<Props>({
  name: 'ZDwsBillingCopayCoordinationFormDialog',
  props: {
    ...getFormPropsOptions(),
    amount: { type: Number, default: 0 },
    dialog: { type: Boolean, default: false }
  },
  setup (props, context) {
    const useDialog = () => {
      const isActive = useSyncedProp('dialog', props, context)
      const close = () => {
        isActive.value = false
      }
      return { isActive, close }
    }
    const { form, observer, submit } = useFormBindings(props, context, {
      processOutput: form => ({
        result: form.result,
        amount: form.amount === undefined ? 0 : +form.amount
      }),
      resetValidatorOnReset: true
    })
    // result が未設定の場合は新規登録とみなす
    const actionText = computed(() => props.value.result ? '編集' : '登録')
    const resultTypes = enumerableOptions(CopayCoordinationResult)
    const isAppropriated = computed(() => form.result === CopayCoordinationResult.appropriated)
    const onChangeResult = (v: CopayCoordinationResult) => {
      // 調整が不要の場合は調整額を 0 にする
      if (v === CopayCoordinationResult.appropriated) {
        form.amount = 0
      } else if (v === CopayCoordinationResult.notCoordinated) {
        form.amount = props.amount
      } else {
        form.amount = undefined
      }
    }
    const rules = computed<Rules>(() => validationRules({
      result: { required },
      amount: { required: !isAppropriated.value, numeric, minValue: 0 }
    }))
    return {
      ...useDialog(),
      actionText,
      form,
      isAppropriated,
      observer,
      onChangeResult,
      resultTypes,
      rules,
      submit
    }
  }
})
</script>
