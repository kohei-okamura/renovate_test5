<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="基本情報">
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
        <z-form-card-item-set :icon="$icons.defrayerCategory">
          <z-form-card-item
            v-slot="{ errors }"
            data-defrayer-category
            vid="defrayerCategory"
            :rules="rules.defrayerCategory"
          >
            <z-select
              v-model="form.defrayerCategory"
              label="公費制度（法別番号） "
              :error-messages="errors"
              :items="defrayerCategories"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.city">
          <z-form-card-item v-slot="{ errors }" data-defrayer-number vid="defrayerNumber" :rules="rules.defrayerNumber">
            <z-text-field v-model.trim="form.defrayerNumber" label="負担者番号 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.recipientNumber">
          <z-form-card-item
            v-slot="{ errors }"
            data-recipient-number
            vid="recipientNumber"
            :rules="rules.recipientNumber"
          >
            <z-text-field v-model.trim="form.recipientNumber" label="受給者番号 *" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.ratio">
          <z-form-card-item
            v-slot="{ errors }"
            data-benefit-rate
            vid="benefitRate"
            :rules="rules.benefitRate"
          >
            <z-text-field v-model.trim="form.benefitRate" label="給付率 *" suffix="%" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.yen">
          <z-form-card-item
            v-slot="{ errors }"
            data-copay
            vid="copay"
            :rules="rules.copay"
          >
            <z-text-field v-model.trim="form.copay" label="本人負担額 *" suffix="円" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { DefrayerCategory } from '@zinger/enums/lib/defrayer-category'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { User } from '~/models/user'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<LtcsSubsidiesApi.Form> & Readonly<{
  buttonText: string
  user: User
}>

export default defineComponent<Props>({
  name: 'ZLtcsSubsidyForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    user: { type: Object, required: true }
  },
  setup: (props, context) => ({
    ...useFormBindings(props, context, {
      init: form => ({
        period: form.period ?? {}
      })
    }),
    defrayerCategories: enumerableOptions(DefrayerCategory),
    rules: validationRules({
      defrayerCategory: { required },
      periodStart: { required },
      periodEnd: { required },
      defrayerNumber: { required, digits: 8 },
      recipientNumber: { required, digits: 7 },
      benefitRate: { required, between: { min: 1, max: 100 } },
      copay: { required, numeric }
    })
  })
})
</script>
