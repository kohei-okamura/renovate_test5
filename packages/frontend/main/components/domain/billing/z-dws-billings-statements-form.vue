<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-data-card
        v-for="(item, i) in statement.aggregates"
        :key="`aggregate_${i}`"
        :title="i === 0 ? '請求額集計欄' : undefined"
      >
        <z-data-card-item
          label="サービス種類コード"
          :icon="$icons.category"
          :value="createDisplayServiceType(item.serviceDivisionCode)"
        />
        <z-data-card-item label="サービス利用日数" :icon="$icons.date" :value="`${item.serviceDays} 日`" />
        <z-data-card-item label="給付単位数" :icon="$icons.score" :value="getDisplayUnit(item.subtotalScore)" />
        <z-data-card-item label="単位数単価" :icon="$icons.yen" :value="`${numeralWithDivision(item.unitCost)} 円`" />
        <z-data-card-item label="総費用額" :value="getDisplayAmount(item.subtotalFee)" />
        <z-data-card-item label="１割相当額" :value="getDisplayAmount(item.unmanagedCopay)" />
        <z-data-card-item label="利用者負担額">
          <z-form-card-item
            v-if="canUpdateContent"
            :ref="`managedCopay_${i}`"
            v-slot="{ errors }"
            class="ml-0 d-inline-flex"
            :data-managed-copay="i"
            :rules="rules.managedCopay"
            :vid="`managedCopay_${i}`"
          >
            <z-text-field
              v-model="form[item.serviceDivisionCode].managedCopay"
              suffix="円"
              type="number"
              :error-messages="errors"
              :min="0"
            />
          </z-form-card-item>
          <span v-else>{{ getDisplayAmount(item.managedCopay) }}</span>
        </z-data-card-item>
        <z-data-card-item label="上限月額調整" :value="getDisplayAmount(item.cappedCopay)" />
        <z-data-card-item label="A型減免：事業者減免額" value="" />
        <z-data-card-item label="A型減免：減免後利用者負担額" value="" />
        <z-data-card-item label="調整後利用者負担額" :value="getDisplayAmount(item.adjustedCopay)" />
        <z-data-card-item label="上限額管理後利用者負担額" :value="getDisplayAmount(item.coordinatedCopay)" />
        <z-data-card-item label="決定利用者負担額" :value="getDisplayAmount(item.subtotalCopay)" />
        <z-data-card-item label="請求額 給付費" :value="getDisplayAmount(item.subtotalBenefit)" />
        <z-data-card-item label="自治体助成分請求額">
          <z-form-card-item
            v-if="canUpdateSubsidy(i)"
            :ref="`subtotalSubsidy_${i}`"
            v-slot="{ errors }"
            class="ml-0 d-inline-flex"
            :data-subtotal-subsidy="i"
            :rules="rules.subtotalSubsidy"
            :vid="`subtotalSubsidy_${i}`"
          >
            <z-text-field
              v-model="form[item.serviceDivisionCode].subtotalSubsidy"
              suffix="円"
              type="number"
              :error-messages="errors"
              :min="0"
            />
          </z-form-card-item>
          <span v-else>{{ getDisplayAmount(item.subtotalSubsidy) }}</span>
        </z-data-card-item>
      </z-data-card>
    </validation-observer>
    <z-data-card title="請求額集計欄：合計">
      <z-data-card-item label="給付単位数" :icon="$icons.score" :value="getDisplayUnit(statement.totalScore)" />
      <z-data-card-item label="総費用額" :icon="$icons.yen" :value="getDisplayAmount(statement.totalFee)" />
      <z-data-card-item label="上限月額調整" :value="getDisplayAmount(statement.totalCappedCopay)" />
      <z-data-card-item label="A型減免：事業者減免額" value="" />
      <z-data-card-item label="A型減免：減免後利用者負担額" value="" />
      <z-data-card-item label="調整後利用者負担額" :value="getDisplayAmount(statement.totalAdjustedCopay)" />
      <z-data-card-item label="上限額管理後利用者負担額" :value="getDisplayAmount(statement.totalCoordinatedCopay)" />
      <z-data-card-item label="決定利用者負担額" :value="getDisplayAmount(statement.totalCopay)" />
      <z-data-card-item label="請求額 給付費" :value="getDisplayAmount(statement.totalBenefit)" />
      <z-data-card-item label="自治体助成分請求額" :value="getDisplayAmount(statement.totalSubsidy)" />
    </z-data-card>
    <z-system-meta-card :id="statement.id" :created-at="statement.createdAt" :updated-at="statement.updatedAt" />
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
import { DwsServiceDivisionCode, resolveDwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { isEmpty, nonEmpty } from '@zinger/helpers'
import { numeral, numeralWithDivision } from '~/composables/numeral'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = FormProps<{ [p: string]: string }> & Readonly<{
  canUpdateContent: boolean
  statement: DwsBillingStatement
}>

export default defineComponent<Props>({
  name: 'DwsBillingStatementsForm',
  props: {
    ...getFormPropsOptions(),
    canUpdateContent: { type: Boolean, required: true },
    statement: { type: Object, required: true }
  },
  setup (props, context) {
    const createDisplayServiceType = (code: DwsServiceDivisionCode) => {
      return `${code}: ${resolveDwsServiceDivisionCode(code)}`
    }
    const numeralOrHyphen = (num?: number) => isEmpty(num) ? '-' : numeral(num)
    const rules = validationRules({
      managedCopay: { required, numeric, minValue: 0 },
      subtotalSubsidy: { required, numeric, minValue: 0 }
    })
    const canUpdateSubsidy = (index: number) => {
      return props.canUpdateContent && props.statement.aggregates[index].subtotalSubsidy !== undefined
    }
    return {
      ...useFormBindings(props, context),
      canUpdateSubsidy,
      createDisplayServiceType,
      getDisplayAmount: (v?: number) => `${numeralOrHyphen(v)} 円`,
      getDisplayUnit: (v: number) => `${numeralOrHyphen(v)} 単位`,
      nonEmpty,
      numeralWithDivision,
      rules
    }
  }
})
</script>
