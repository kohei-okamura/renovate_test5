<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
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
        <z-form-card-item-set>
          <z-form-card-item
            v-slot="{ errors }"
            data-specified-office-addition
            vid="specifiedOfficeAddition"
            :rules="rules.specifiedOfficeAddition"
          >
            <z-select
              v-model="form.specifiedOfficeAddition"
              label="特定事業所加算"
              :error-messages="errors"
              :items="specifiedOfficeAdditions"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set>
          <z-form-card-item
            v-slot="{ errors }"
            data-treatment-improvement-addition
            vid="treatmentImprovementAddition"
            :rules="rules.treatmentImprovementAddition"
          >
            <z-select
              v-model="form.treatmentImprovementAddition"
              label="処遇改善加算"
              :error-messages="errors"
              :items="treatmentImprovementAdditions"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set>
          <z-form-card-item
            v-slot="{ errors }"
            data-specified-treatment-improvement-addition
            vid="specifiedTreatmentImprovementAddition"
            :rules="rules.specifiedTreatmentImprovementAddition"
          >
            <z-select
              v-model="form.specifiedTreatmentImprovementAddition"
              label="特定処遇改善加算"
              :error-messages="errors"
              :items="specifiedTreatmentImprovementAdditions"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set>
          <z-form-card-item
            v-slot="{ errors }"
            data-base-increase-support-addition
            vid="baseIncreaseSupportAddition"
            :rules="rules.baseIncreaseSupportAddition"
          >
            <z-select
              v-model="form.baseIncreaseSupportAddition"
              label="ベースアップ等支援加算"
              :error-messages="errors"
              :items="baseIncreaseSupportAdditions"
            />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { DwsBaseIncreaseSupportAddition } from '@zinger/enums/lib/dws-base-increase-support-addition'
import {
  DwsSpecifiedTreatmentImprovementAddition
} from '@zinger/enums/lib/dws-specified-treatment-improvement-addition'
import { DwsTreatmentImprovementAddition } from '@zinger/enums/lib/dws-treatment-improvement-addition'
import {
  VisitingCareForPwsdSpecifiedOfficeAddition
} from '@zinger/enums/lib/visiting-care-for-pwsd-specified-office-addition'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { Office } from '~/models/office'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<VisitingCareForPwsdCalcSpecsApi.Form> & Readonly<{
  buttonText: string
  office: Office
}>

export default defineComponent<Props>({
  name: 'ZVisitingCareForPwsdCalcSpecForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    office: { type: Object, required: true }
  },
  setup: (props, context) => ({
    ...useFormBindings(props, context, {
      init: form => ({
        period: form.period ?? {}
      })
    }),
    rules: validationRules({
      periodStart: { required },
      periodEnd: { required },
      baseIncreaseSupportAddition: { required },
      specifiedOfficeAddition: { required },
      specifiedTreatmentImprovementAddition: { required },
      treatmentImprovementAddition: { required }
    }),
    baseIncreaseSupportAdditions: enumerableOptions(DwsBaseIncreaseSupportAddition),
    specifiedOfficeAdditions: enumerableOptions(VisitingCareForPwsdSpecifiedOfficeAddition),
    specifiedTreatmentImprovementAdditions: enumerableOptions(DwsSpecifiedTreatmentImprovementAddition),
    treatmentImprovementAdditions: enumerableOptions(DwsTreatmentImprovementAddition)
  })
})
</script>
