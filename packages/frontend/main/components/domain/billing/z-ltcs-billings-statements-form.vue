<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <v-container class="pa-0">
        <v-row dense>
          <v-col
            v-for="(item, i) in statement.aggregates"
            :key="item.serviceDivisionCode"
            class="d-flex flex-column align-stretch"
            cols="12"
            sm="6"
          >
            <z-data-card title="請求額集計欄">
              <z-data-card-item label="サービス種類コード/名称" :icon="$icons.category">
                <span>{{ item.serviceDivisionCode }}:</span>
                <span>{{ resolveLtcsServiceDivisionCode(item.serviceDivisionCode) }}</span>
              </z-data-card-item>
              <z-data-card-item label="サービス実日数" :icon="$icons.days">
                <span>{{ item.serviceDays }} 日</span>
              </z-data-card-item>
              <z-data-card-item label="計画単位数" :icon="$icons.score">
                <z-form-card-item
                  v-if="canUpdateContent"
                  :ref="`plannedScore_${i}`"
                  v-slot="{ errors }"
                  class="ml-0 pr-12"
                  :custom-messages="errorMessages.plannedScore"
                  :data-planned-score="i"
                  :rules="rules[`plannedScore_${i}`]"
                  :vid="`plannedScore_${i}`"
                >
                  <z-text-field
                    v-model="form[item.serviceDivisionCode]"
                    suffix="単位"
                    :error-messages="errors"
                    :min="0"
                  />
                </z-form-card-item>
                <span v-else>{{ numeral(item.plannedScore) }} 単位</span>
              </z-data-card-item>
              <z-data-card-item label="限度額管理対象単位数">
                <span>{{ numeral(item.managedScore) }} 単位</span>
              </z-data-card-item>
              <z-data-card-item label="限度額管理対象外単位数">
                <span>{{ numeral(item.unmanagedScore) }} 単位</span>
              </z-data-card-item>
              <z-data-card-item label="給付単位数">
                <span>{{ numeral(item.insurance.totalScore) }} 単位</span>
              </z-data-card-item>
              <z-data-card-item label="単位数単価" :icon="$icons.yen">
                <span>{{ numeralWithDivision(item.insurance.unitCost) }} 円</span>
              </z-data-card-item>
              <z-data-card-item label="保険請求額">
                <span>{{ numeral(item.insurance.claimAmount) }} 円</span>
              </z-data-card-item>
              <z-data-card-item label="利用者負担額">
                <span>{{ numeral(item.insurance.copayAmount) }} 円</span>
              </z-data-card-item>
              <template v-for="(subsidy, j) in item.subsidies">
                <z-data-card-item :key="`subsidy-claimAmount-${j}`" label="公費請求額">
                  <span>{{ numeral(subsidy.claimAmount) }} 円</span>
                </z-data-card-item>
                <z-data-card-item :key="`subsidy-copayAmount-${j}`" label="公費分本人負担">
                  <span>{{ numeral(subsidy.copayAmount) }} 円</span>
                </z-data-card-item>
              </template>
            </z-data-card>
          </v-col>
          <v-col class="d-flex flex-column align-stretch" cols="12" sm="6">
            <z-data-card title="合計">
              <z-data-card-item label="保険給付率" :icon="$icons.ratio">
                <span>{{ numeral(statement.insurance.benefitRate) }} %</span>
              </z-data-card-item>
              <z-data-card-item v-for="subsidy in statement.subsidies" :key="subsidy.defrayerNumber" label="公費給付率">
                <span>{{ numeral(subsidy.benefitRate) }} %</span>
              </z-data-card-item>
              <z-data-card-item label="保険請求額" :icon="$icons.yen">
                <span>{{ numeral(statement.insurance.claimAmount) }} 円</span>
              </z-data-card-item>
              <z-data-card-item label="利用者負担額">
                <span>{{ numeral(statement.insurance.copayAmount) }} 円</span>
              </z-data-card-item>
              <template v-for="(subsidy, i) in statement.subsidies">
                <z-data-card-item :key="`subsidy-claimAmount-${i}`" label="公費請求額">
                  <span>{{ numeral(subsidy.claimAmount) }} 円</span>
                </z-data-card-item>
                <z-data-card-item :key="`subsidy-copayAmount-${i}`" label="公費分本人負担">
                  <span>{{ numeral(subsidy.copayAmount) }} 円</span>
                </z-data-card-item>
              </template>
            </z-data-card>
          </v-col>
        </v-row>
      </v-container>
    </validation-observer>
    <z-form-action-button
      v-if="canUpdateContent"
      ref="saveButton"
      text="保存"
      :disabled="progress"
      :fixed="true"
      :icon="$icons.save"
      :loading="progress"
    />
  </v-form>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { resolveLtcsServiceDivisionCode } from '@zinger/enums/lib/ltcs-service-division-code'
import { numeral, numeralWithDivision } from '~/composables/numeral'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<{ [p: string]: string }> & Readonly<{
  canUpdateContent: boolean
  statement: LtcsBillingStatement
}>

export default defineComponent<Props>({
  name: 'LtcsBillingStatementsForm',
  props: {
    ...getFormPropsOptions(),
    canUpdateContent: { type: Boolean, required: true },
    statement: { type: Object, required: true }
  },
  setup (props, context) {
    const aggregates = props.statement.aggregates ?? []
    const errorMessages = {
      plannedScore: {
        maxValue: '限度額管理対象単位数以下の値を入力してください。'
      }
    }
    const rules = validationRules(Object.fromEntries(aggregates.map((x, i) => {
      return [`plannedScore_${i}`, { required, numeric, minValue: 0, maxValue: x.managedScore }]
    })))
    return {
      ...useFormBindings(props, context),
      errorMessages,
      numeral: (x: number | '-' = '-', format: string = '0,0') => numeral(x, format),
      numeralWithDivision,
      resolveLtcsServiceDivisionCode,
      rules
    }
  }
})
</script>
