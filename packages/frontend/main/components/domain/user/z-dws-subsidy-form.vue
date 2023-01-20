<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="自治体助成情報">
        <z-form-card-item-set :icon="$icons.dateRange">
          <z-flex>
            <z-form-card-item v-slot="{ errors }" data-period-start vid="periodStart" :rules="rules.periodStart">
              <z-date-field v-model="form.period.start" label="適用期間 *" :error-messages="errors" />
            </z-form-card-item>
            <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
            <z-form-card-item v-slot="{ errors }" data-period-end vid="periodEnd" :rules="rules.periodEnd">
              <z-date-field v-model="form.period.end" :error-messages="errors" />
            </z-form-card-item>
          </z-flex>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.city">
          <v-row no-gutters>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-city-name vid="cityName" :rules="rules.cityName">
                <z-text-field v-model.trim="form.cityName" label="助成自治体名 *" :error-messages="errors" />
              </z-form-card-item>
            </v-col>
            <v-col cols="12" sm="6">
              <z-form-card-item v-slot="{ errors }" data-city-code vid="cityCode" :rules="rules.cityCode">
                <z-text-field v-model.trim="form.cityCode" label="助成自治体番号 *" :error-messages="errors" />
              </z-form-card-item>
            </v-col>
          </v-row>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.category">
          <v-row no-gutters>
            <v-col>
              <z-form-card-item v-slot="{ errors }" data-subsidy-type vid="subsidyType" :rules="rules.subsidyType">
                <z-select
                  v-model="form.subsidyType"
                  label="給付方式 *"
                  :error-messages="errors"
                  :items="subsidyTypes"
                />
              </z-form-card-item>
            </v-col>
          </v-row>
          <template v-if="hasBenefitRate">
            <v-row no-gutters>
              <v-col cols="12" sm="5">
                <z-flex>
                  <z-form-card-item v-slot="{ errors }" data-factor vid="factor" :rules="rules.factor">
                    <z-select
                      v-model="form.factor"
                      label="基準値 *"
                      :error-messages="errors"
                      :items="factors"
                    />
                  </z-form-card-item>
                  <z-flex-shrink class="pb-2 pl-2 pt-4">の</z-flex-shrink>
                </z-flex>
              </v-col>
              <v-col cols="12" sm="7">
                <z-flex>
                  <z-form-card-item v-slot="{ errors }" data-benefit-rate vid="benefitRate" :rules="rules.benefitRate">
                    <z-text-field
                      v-model.trim="form.benefitRate"
                      label="給付率 *"
                      suffix="%"
                      type="number"
                      :error-messages="errors"
                    />
                  </z-form-card-item>
                  <z-flex-shrink class="pb-2 pl-2 pt-4">を自治体が負担する</z-flex-shrink>
                </z-flex>
              </v-col>
            </v-row>
            <v-row no-gutters>
              <v-col cols="12">
                <z-form-card-item v-slot="{ errors }" data-rounding vid="rounding" :rules="rules.rounding">
                  <z-select
                    v-model="form.rounding"
                    label="端数処理 *"
                    :error-messages="errors"
                    :items="roundings"
                  />
                </z-form-card-item>
              </v-col>
            </v-row>
          </template>
          <template v-if="hasCopayRate">
            <v-row no-gutters>
              <v-col cols="12" sm="7">
                <z-flex>
                  <z-flex-shrink class="pb-2 pl-2 pt-4">利用者負担額を</z-flex-shrink>
                  <z-form-card-item v-slot="{ errors }" data-factor vid="factor" :rules="rules.factor">
                    <z-select
                      v-model="form.factor"
                      label="基準値 *"
                      :error-messages="errors"
                      :items="factors"
                    />
                  </z-form-card-item>
                  <z-flex-shrink class="pb-2 pl-2 pt-4">の</z-flex-shrink>
                </z-flex>
              </v-col>
              <v-col cols="12" sm="5">
                <z-flex>
                  <z-form-card-item v-slot="{ errors }" data-copay-rate vid="copayRate" :rules="rules.copayRate">
                    <z-text-field
                      v-model.trim="form.copayRate"
                      label="負担率 *"
                      suffix="%"
                      type="number"
                      :error-messages="errors"
                    />
                  </z-form-card-item>
                  <z-flex-shrink class="pb-2 pl-2 pt-4">に軽減する</z-flex-shrink>
                </z-flex>
              </v-col>
            </v-row>
            <v-row no-gutters>
              <v-col cols="12">
                <z-form-card-item v-slot="{ errors }" data-rounding vid="rounding" :rules="rules.rounding">
                  <z-select
                    v-model="form.rounding"
                    label="端数処理 *"
                    :error-messages="errors"
                    :items="roundings"
                  />
                </z-form-card-item>
              </v-col>
            </v-row>
          </template>
          <template v-if="hasBenefitAmount">
            <v-row no-gutters>
              <v-col>
                <z-form-card-item
                  v-slot="{ errors }"
                  data-benefit-amount
                  vid="benefitAmount"
                  :rules="rules.benefitAmount"
                >
                  <z-text-field
                    v-model.trim="form.benefitAmount"
                    label="給付額 *"
                    suffix="円"
                    type="number"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </v-col>
            </v-row>
          </template>
          <template v-if="hasCopayAmount">
            <v-row no-gutters>
              <v-col>
                <z-form-card-item v-slot="{ errors }" data-copay-amount vid="copayAmount" :rules="rules.copayAmount">
                  <z-text-field
                    v-model.trim="form.copayAmount"
                    label="本人負担額 *"
                    suffix="円"
                    type="number"
                    :error-messages="errors"
                  />
                </z-form-card-item>
              </v-col>
            </v-row>
          </template>
        </z-form-card-item-set>
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
import { computed, defineComponent, watch } from '@nuxtjs/composition-api'
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { UserDwsSubsidyType } from '@zinger/enums/lib/user-dws-subsidy-type'
import { isEmpty } from '@zinger/helpers'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { User } from '~/models/user'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'
import { $datetime } from '~/services/datetime-service'
import { numeric, required } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<DwsSubsidiesApi.Form> & Readonly<{
  buttonText: string
  user: User
}>

export default defineComponent<Props>({
  name: 'ZDwsSubsidyForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    user: { type: Object, required: true }
  },
  setup: (props, context) => {
    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        period: form.period ?? {},
        subsidyType: form.subsidyType,
        factor: form.factor === UserDwsSubsidyFactor.none ? undefined : form.factor,
        benefitRate: form.benefitRate,
        copayRate: form.copayRate,
        rounding: form.rounding === Rounding.none ? undefined : form.rounding,
        benefitAmount: form.benefitAmount,
        copayAmount: form.copayAmount
      })
    })
    const useSelectOptions = () => ({
      subsidyTypes: enumerableOptions(UserDwsSubsidyType)
    })
    const flags = {
      hasBenefitRate: computed(() => form.subsidyType === UserDwsSubsidyType.benefitRate),
      hasCopayRate: computed(() => form.subsidyType === UserDwsSubsidyType.copayRate),
      hasBenefitAmount: computed(() => form.subsidyType === UserDwsSubsidyType.benefitAmount),
      hasCopayAmount: computed(() => form.subsidyType === UserDwsSubsidyType.copayAmount)
    }
    const watchFlags = <K extends keyof typeof flags> (property: K, fn: () => void) => {
      watch(() => flags[property].value, value => { if (!value) { fn() } })
    }
    // 給付方式が定率給付以外の場合は値をundefinedにする
    watchFlags('hasBenefitRate', () => {
      form.benefitRate = undefined
      form.factor = undefined
      form.rounding = undefined
    })
    // 給付方式が定率負担以外の場合は値をundefinedにする
    watchFlags('hasCopayRate', () => {
      form.copayRate = undefined
      form.factor = undefined
      form.rounding = undefined
    })
    // 給付方式が定額給付以外の場合は値をundefinedにする
    watchFlags('hasBenefitAmount', () => { form.benefitAmount = undefined })
    // 給付方式が定額負担以外の場合は値をundefinedにする
    watchFlags('hasCopayAmount', () => { form.copayAmount = undefined })
    const rules = computed<Rules>(() => {
      const periodStart = form.period?.start
      const periodEnd = form.period?.end
      const customPeriod = {
        message: '開始日より終了日の日付を後にしてください。',
        validate: () =>
          isEmpty(periodStart) ||
          isEmpty(periodEnd) ||
          $datetime.parse(periodStart) < $datetime.parse(periodEnd)
      }
      const hasBenefitRate = flags.hasBenefitRate.value
      const hasCopayRate = flags.hasCopayRate.value
      const hasBenefitAmount = flags.hasBenefitAmount.value
      const hasCopayAmount = flags.hasCopayAmount.value
      return validationRules({
        periodStart: { custom: customPeriod, required },
        periodEnd: { custom: customPeriod, required },
        cityName: { required, max: process.env.cityMaxLength },
        cityCode: { required, digits: 6 },
        subsidyType: { required },
        factor: { required: hasBenefitRate || hasCopayRate },
        benefitRate: { required: hasBenefitRate, between: { min: 1, max: 100 } },
        copayRate: { required: hasCopayRate, between: { min: 1, max: 100 } },
        rounding: { required: hasBenefitRate || hasCopayRate },
        benefitAmount: { required: hasBenefitAmount, numeric },
        copayAmount: { required: hasCopayAmount, numeric },
        note: { max: 255 }
      })
    })
    return {
      ...useSelectOptions(),
      ...flags,
      factors: enumerableOptions(UserDwsSubsidyFactor).filter(x => x.value !== UserDwsSubsidyFactor.none),
      roundings: enumerableOptions(Rounding).filter(x => x.value !== Rounding.none),
      form,
      observer,
      rules,
      submit
    }
  }
})
</script>
