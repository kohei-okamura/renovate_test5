<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="基本情報">
        <v-alert class="text-caption text-sm-body-2 mb-3 mx-4" dense type="info">
          一度登録した事業所・単位時間数・課税区分・税率区分・費用は変更できません。
        </v-alert>
        <z-form-card-item-set :icon="$icons.office">
          <z-form-card-item data-office-id>
            <z-keyword-filter-autocomplete
              v-model="form.officeId"
              label="事業所"
              :disabled="isEdit"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.ownExpenseProgram">
          <z-form-card-item v-slot="{ errors }" data-name vid="name" :rules="rules.name">
            <z-text-field v-model.trim="form.name" label="自費サービス名 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.timeAmount">
          <z-form-card-item
            v-slot="{ errors }"
            data-duration-minutes
            vid="durationMinutes"
            :rules="rules.durationMinutes"
          >
            <z-text-field
              v-model.trim="durationMinutes"
              label="単位時間数 *"
              suffix="分"
              :disabled="isEdit"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.category">
          <z-form-card-item v-slot="{ errors }" data-tax-type vid="feeTaxType" :rules="rules.taxType">
            <z-select
              v-model="form.fee.taxType"
              label="課税区分 *"
              :disabled="isEdit"
              :error-messages="errors"
              :items="taxType"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set v-if="hasTaxExempted" :icon="$icons.yen">
          <z-form-card-item
            v-slot="{ errors }"
            data-tax-exempted-fee
            vid="taxExemptedFee"
            :rules="rules.taxExemptedFee"
          >
            <z-text-field
              v-model.trim="taxExemptedFee"
              data-tax-exempted-fee-input
              label="費用 *"
              suffix="円"
              :disabled="isEdit"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <template v-if="hasTaxExcluded || hasTaxIncluded">
          <z-form-card-item-set :icon="$icons.yen">
            <z-form-card-item
              v-slot="{ errors }"
              data-tax-excluded
              vid="feeTaxExcluded"
              :rules="rules.taxExcluded"
            >
              <z-text-field
                v-model.trim="taxExcluded"
                data-tax-excluded-input
                label="費用（税抜） *"
                suffix="円"
                :disabled="hasTaxIncluded || isEdit"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item v-slot="{ errors }" data-tax-included vid="feeTaxIncluded" :rules="rules.taxIncluded">
              <z-text-field
                v-model.trim="taxIncluded"
                data-tax-included-input
                label="費用（税込） *"
                suffix="円"
                :disabled="hasTaxExcluded || isEdit"
                :error-messages="errors"
              />
            </z-form-card-item>
            <z-form-card-item
              v-slot="{ errors }"
              data-tax-category
              vid="feeTaxCategory"
              :rules="rules.taxCategory"
            >
              <z-select
                v-model="form.fee.taxCategory"
                data-tax-category-input
                label="税率区分 *"
                :disabled="isEdit"
                :error-messages="errors"
                :items="taxCategory"
              />
            </z-form-card-item>
          </z-form-card-item-set>
        </template>
        <z-form-card-item-set :icon="$icons.text">
          <z-form-card-item v-slot="{ errors }" data-note vid="note" :rules="rules.note">
            <z-textarea v-model.trim="form.note" label="備考" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { TaxCategory } from '@zinger/enums/lib/tax-category'
import { TaxType } from '@zinger/enums/lib/tax-type'
import { assign } from '@zinger/helpers'
import { toNarrowAlphanumeric } from 'jaco'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { OwnExpenseProgramsApi } from '~/services/api/own-expense-programs-api'
import { numeric, required } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DeepPartial<OwnExpenseProgramsApi.Form>> & Readonly<{
  buttonText: string
  permission: Permission
}>

const ONE_DAY_MINUTE = 1440
const ALL_OFFICE = 0

export default defineComponent<Props>({
  name: 'ZOwnExpenseProgramForm',
  props: {
    ...getFormPropsOptions(),
    isEdit: { type: Boolean, default: false },
    buttonText: { type: String, required: true },
    permission: { type: String, required: true }
  },
  setup (props, context) {
    const propRefs = toRefs(props)

    const numericForm = reactive({
      durationMinutes: props.value.durationMinutes ?? '' as string | number,
      fee: {
        taxExcluded: props.value.fee?.taxExcluded ?? '' as string | number,
        taxIncluded: props.value.fee?.taxIncluded ?? '' as string | number
      }
    })

    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        officeId: form.officeId ?? ALL_OFFICE,
        name: form.name ?? '',
        fee: {
          taxType: form.fee?.taxType,
          taxCategory: form.fee?.taxCategory
        },
        note: form.note ?? ''
      }),
      processOutput: output => {
        output.durationMinutes = numericForm.durationMinutes as number
        const taxExcluded = numericForm.fee.taxExcluded as number
        const taxIncluded = numericForm.fee.taxIncluded as number
        assign(output.fee, { taxExcluded, taxIncluded })
        return {
          ...output,
          officeId: output.officeId === ALL_OFFICE ? undefined : output.officeId
        }
      }
    })

    const taxRate = computed(() => {
      switch (form.fee?.taxCategory) {
        case TaxCategory.consumptionTax:
          return Number(process.env.consumptionTax)
        case TaxCategory.reducedConsumptionTax:
          return Number(process.env.reducedConsumptionTax)
        default:
          return 1
      }
    })

    const calculateFee = {
      taxExcluded: (value: number) => Math.ceil(value / taxRate.value),
      taxIncluded: (value: number) => Math.floor(value * taxRate.value)
    }

    const correctValue = (value: number | string) => {
      const halfNumValue = typeof value === 'string' ? parseInt(toNarrowAlphanumeric(value)) : value
      return isNaN(halfNumValue) ? value : halfNumValue
    }

    const useSelectOptions = () => ({
      taxType: enumerableOptions(TaxType),
      taxCategory: enumerableOptions(TaxCategory).filter(option => option.text !== '該当なし')
    })

    const flags = {
      hasTaxExcluded: computed(() => form.fee?.taxType === TaxType.taxExcluded),
      hasTaxIncluded: computed(() => form.fee?.taxType === TaxType.taxIncluded),
      hasTaxExempted: computed(() => form.fee?.taxType === TaxType.taxExempted)
    }

    const updateFee = (inputValue: number | string) => {
      const value = typeof inputValue === 'string' ? correctValue(inputValue) : inputValue
      const taxExcluded = flags.hasTaxIncluded.value ? (typeof value === 'number' ? calculateFee.taxExcluded(value) : '') : value
      const taxIncluded = flags.hasTaxExcluded.value ? (typeof value === 'number' ? calculateFee.taxIncluded(value) : '') : value
      numericForm.fee = { taxExcluded, taxIncluded }
    }

    const durationMinutes = computed({
      get: () => numericForm.durationMinutes,
      set: value => {
        numericForm.durationMinutes = correctValue(value)
      }
    })

    const taxExcluded = computed({
      get: () => numericForm.fee.taxExcluded,
      set: value => updateFee(value)
    })

    const taxIncluded = computed({
      get: () => numericForm.fee.taxIncluded,
      set: value => updateFee(value)
    })
    const taxExemptedFee = computed({
      get: () => numericForm.fee.taxExcluded,
      set: value => updateFee(value)
    })

    const watchFlags = <K extends keyof typeof flags> (property: K, fn: () => void) => {
      watch(() => flags[property].value, value => { if (value) { fn() } })
    }
    watchFlags('hasTaxExcluded', () => {
      numericForm.fee = { taxExcluded: '', taxIncluded: '' }
      assign(form.fee, { taxCategory: undefined })
    })
    watchFlags('hasTaxIncluded', () => {
      numericForm.fee = { taxExcluded: '', taxIncluded: '' }
      assign(form.fee, { taxCategory: undefined })
    })
    watchFlags('hasTaxExempted', () => {
      numericForm.fee = { taxExcluded: '', taxIncluded: '' }
      assign(form.fee, { taxCategory: TaxCategory.unapplicable })
    })
    watch(() => form.fee?.taxCategory, () => {
      const value = flags.hasTaxIncluded.value ? numericForm.fee.taxIncluded : numericForm.fee.taxExcluded
      updateFee(value)
    })

    const rules = computed<Rules>(() => {
      const hasTaxExcluded = flags.hasTaxExcluded.value
      const hasTaxIncluded = flags.hasTaxIncluded.value
      const hasTaxExempted = flags.hasTaxExempted.value
      return validationRules({
        name: { required, max: 200 },
        durationMinutes: { required, numeric, between: { min: 1, max: ONE_DAY_MINUTE } },
        taxExemptedFee: { required: hasTaxExempted, numeric },
        taxExcluded: { required: hasTaxExcluded, numeric },
        taxIncluded: { required: hasTaxIncluded, numeric },
        taxType: { required },
        taxCategory: { required: !hasTaxExempted },
        note: { max: 255 }
      })
    })

    const { isLoadingOffices, officeOptions } = useOffices({ permission: propRefs.permission, internal: true })
    const options = computed(() => [
      { keyword: 'スベテノジギョウショ', text: 'すべての事業所', value: ALL_OFFICE },
      ...officeOptions.value
    ])

    return {
      ...useSelectOptions(),
      ...flags,
      durationMinutes,
      form,
      isLoadingOffices,
      observer,
      officeOptions: options,
      rules,
      submit,
      taxExcluded,
      taxExemptedFee,
      taxIncluded
    }
  }
})
</script>
