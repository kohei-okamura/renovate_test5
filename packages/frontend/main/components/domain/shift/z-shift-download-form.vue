<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-form-card title="アップロード用エクセルファイル">
        <z-form-card-item-set no-icon>
          <p>条件を設定し《ダウンロード》ボタンを押してください。</p>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.office">
          <z-form-card-item v-slot="{ errors }" data-office-id vid="officeId" :rules="rules.officeId">
            <z-keyword-filter-autocomplete
              v-model="form.officeId"
              label="事業所 *"
              :disabled="progress"
              :error-messages="errors"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dateRange">
          <z-form-card-item data-range-type vid="rangeType" :rules="rules.rangeType">
            <v-radio-group v-model="rangeType">
              <template #label>
                <div>期間</div>
              </template>
              <v-row no-gutters>
                <v-col v-for="x in rangeTypes" :key="x.value">
                  <v-radio :label="x.text" :value="x.value" />
                </v-col>
              </v-row>
            </v-radio-group>
          </z-form-card-item>
          <v-row v-if="rangeIsRequired" no-gutters>
            <v-col>
              <z-form-card-item v-slot="{ errors }" data-range-start vid="rangeStart" :rules="rules.range.start">
                <z-date-field v-model="form.range.start" label="開始日" :error-messages="errors" :min="rangeStartMin" />
              </z-form-card-item>
            </v-col>
            <v-col>
              <z-form-card-item v-slot="{ errors }" data-range-end vid="rangeEnd" :rules="rules.range.end">
                <z-date-field v-model="form.range.end" label="終了日" :error-messages="errors" :min="rangeEndMin" />
              </z-form-card-item>
            </v-col>
          </v-row>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.copy">
          <z-form-card-item data-source-type vid="sourceType" :rules="rules.sourceType">
            <v-radio-group v-model="sourceType">
              <template #label>
                <div>過去の勤務シフトをコピー</div>
              </template>
              <v-row no-gutters>
                <v-col v-for="x in sourceTypes" :key="x.value">
                  <v-radio :label="x.text" :value="x.value" />
                </v-col>
              </v-row>
            </v-radio-group>
          </z-form-card-item>
        </z-form-card-item-set>
        <z-action-button text="ダウンロード" :disabled="progress" :icon="$icons.download" :loading="progress" />
      </z-form-card>
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, toRefs } from '@nuxtjs/composition-api'
import { DateRangeType } from '@zinger/enums/lib/date-range-type'
import { Permission } from '@zinger/enums/lib/permission'
import { enumerableOptions } from '~/composables/enumerable-options'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { expandDateRange } from '~/models/date-range'
import { ShiftsApi } from '~/services/api/shifts-api'
import { required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Form = ShiftsApi.CreateTemplateForm
type Props = FormProps<Form>

const NO_COPY = -1

export default defineComponent<Props>({
  name: 'ZShiftDownloadForm',
  props: {
    ...getFormPropsOptions()
  },
  setup: (props: Props, context) => {
    const state = reactive({
      rangeType: DateRangeType.nextMonth as DateRangeType,
      sourceType: NO_COPY as DateRangeType | (typeof NO_COPY)
    })
    const { $datetime } = usePlugins()
    const { form, observer, submit } = useFormBindings(props, context, {
      processOutput: form => ({
        ...form,
        range: expandDateRange({
          ...form.range,
          dateRangeType: state.rangeType
        }),
        isCopy: state.sourceType !== NO_COPY,
        source: state.sourceType !== NO_COPY ? expandDateRange({ dateRangeType: state.sourceType }) : undefined
      })
    })
    const dateRangeTypes = enumerableOptions(DateRangeType)
    const rangeTypes = dateRangeTypes.filter(x => {
      const xs: DateRangeType[] = [
        DateRangeType.nextWeek,
        DateRangeType.nextMonth,
        DateRangeType.specify
      ]
      return xs.includes(x.value)
    })
    const sourceTypes = [
      { value: NO_COPY, text: 'コピーしない' },
      ...dateRangeTypes.filter(x => {
        const xs: DateRangeType[] = [
          DateRangeType.thisWeek,
          DateRangeType.lastWeek
        ]
        return xs.includes(x.value)
      })
    ]
    const rangeIsRequired = computed(() => state.rangeType === DateRangeType.specify)
    const tomorrow = $datetime.now.plus({ days: 1 })
    const rangeStartMin = tomorrow.toISODate()
    const rangeEndMin = computed(() => form.range?.start ?? rangeStartMin)
    const rules = computed(() => validationRules({
      officeId: { required },
      rangeType: { required },
      range: {
        start: { required: rangeIsRequired.value, minDate: rangeStartMin },
        end: { required: rangeIsRequired.value, minDate: [rangeEndMin.value, '開始日'] }
      },
      sourceType: { required }
    }))
    return {
      ...toRefs(state),
      ...useOffices({ permission: Permission.importShifts, internal: true }),
      form,
      observer,
      rangeEndMin,
      rangeIsRequired,
      rangeStartMin,
      rangeTypes,
      rules,
      sourceTypes,
      submit
    }
  }
})
</script>
