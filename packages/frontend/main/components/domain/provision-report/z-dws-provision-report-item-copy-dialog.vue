<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog max-width="640" persistent transition="dialog" :value="show" :width="width" @input="v => v || cancel()">
    <v-card>
      <z-card-titlebar color="blue-grey">{{ title }}</z-card-titlebar>
      <z-data-card-item label="サービス提供時間" :icon="$icons.schedule">
        <z-datetime :value="value.schedule.start" />
        <span>〜</span>
        <z-time :value="value.schedule.end" />
      </z-data-card-item>
      <z-data-card-item
        label="サービス区分"
        :icon="$icons.category"
        :value="resolveDwsProjectServiceCategory(value.category)"
      />
      <z-data-card-item
        v-if="isOwnExpense"
        label="自費サービス"
        :value="resolveOwnExpenseProgramName(value.ownExpenseProgramId)"
      />
      <z-data-card-item
        v-if="isPwsd"
        label="移動介護時間数"
        :icon="$icons.timeAmount"
        :value="`${value.movingDurationMinutes || 0} 分`"
      />
      <z-data-card-item label="提供人数" :icon="$icons.headcount" :value="`${value.headcount} 人`" />
      <z-data-card-item label="サービスオプション" :icon="$icons.serviceOption">
        <template v-if="value.options.length !== 0">
          <v-chip v-for="x in value.options" :key="x" label small>{{ resolveServiceOption(x) }}</v-chip>
        </template>
        <span v-else>-</span>
      </z-data-card-item>
      <z-data-card-item label="備考" :icon="$icons.note" :value="value.note || '-'" />
      <v-divider class="mt-3 mb-6 mx-4" />
      <v-form data-form @submit.prevent="submit">
        <div class="ml-6 ml-sm-16 mr-4 text-body-2 text-sm-body-1">コピー先の日付を選択してください（複数選択可）。</div>
        <validation-observer ref="observer" class="ml-4 ml-sm-6 mr-4 pb-6" tag="div">
          <z-date-picker
            v-model="form.dates"
            :allowed-dates="isCopyable"
            :max="month.last"
            :min="month.first"
            :multiple="true"
            next-icon=""
            :picker-date="month.month"
            prev-icon=""
            full-width
          />
          <z-validate-error-messages
            v-slot="{ errors }"
            v-model="form.dates.length"
            data-dates-length
            class="mb-4 mx-4 mt-2"
            vid="dates"
            :rules="rules.dates"
          >
            <z-error-container class="error--text v-messages">
              {{ errors[0] }}
            </z-error-container>
          </z-validate-error-messages>
          <v-row class="pr-4" justify="center" justify-md="end" no-gutters>
            <v-col cols="5" md="3">
              <v-btn data-cancel text width="100%" @click.stop="cancel">キャンセル</v-btn>
            </v-col>
            <v-col class="pl-4" cols="5" md="3">
              <v-btn color="primary" data-ok depressed type="submit" width="100%">コピー</v-btn>
            </v-col>
          </v-row>
        </validation-observer>
      </v-form>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { reactive } from '@vue/composition-api'
import {
  DwsProjectServiceCategory,
  resolveDwsProjectServiceCategory
} from '@zinger/enums/lib/dws-project-service-category'
import { resolveServiceOption } from '@zinger/enums/lib/service-option'
import { eraDate } from '~/composables/era-date'
import { useOwnExpenseProgramResolverStore } from '~/composables/stores/use-own-expense-program-resolver-store'
import { DateLike } from '~/models/date'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { $datetime } from '~/services/datetime-service'
import { observerRef } from '~/support/reactive'
import { validationRules } from '~/support/validation/utils'

type Form = {
  dates: DateLike[]
}

type Props = {
  copyableDates: DateLike[]
  show: boolean
  target: string
  value: DwsProvisionReportItem
  width: string
}

export default defineComponent<Props>({
  name: 'ZDwsProvisionReportItemCopyDialog',
  props: {
    copyableDates: { type: Array, required: true },
    show: { type: Boolean, required: true },
    target: { type: String, required: true },
    value: { type: Object, required: true },
    width: { type: String, required: true }
  },
  setup (props: Props, context) {
    const isPwsd = computed(() => props.value.category === DwsProjectServiceCategory.visitingCareForPwsd)
    const isOwnExpense = computed(() => props.value.category === DwsProjectServiceCategory.ownExpense)
    const providedIn = $datetime.parse(props.value.schedule.date)
    const month = {
      month: providedIn.toISODate(),
      first: providedIn.startOf('month').toISODate(),
      last: providedIn.endOf('month').toISODate()
    }
    const form = reactive<Form>({
      dates: []
    })
    const observer = observerRef()
    const title = computed(() => `${props.target}をコピー`)
    const submit = async () => {
      if (await observer.value?.validate()) {
        context.emit('click:save', form.dates)
      }
    }
    const cancel = () => context.emit('click:cancel')
    // コピー可能な日付
    const parsedCopyableDates = props.copyableDates.map(x => {
      return typeof x === 'string' ? x : $datetime.parse(x).toISODate()
    })
    const isCopyable = (x: string) => parsedCopyableDates.includes(x)
    return {
      cancel,
      eraDate,
      form,
      isCopyable,
      isOwnExpense,
      isPwsd,
      month,
      resolveDwsProjectServiceCategory,
      resolveOwnExpenseProgramName: useOwnExpenseProgramResolverStore().state.resolveOwnExpenseProgramName,
      resolveServiceOption,
      rules: validationRules({
        dates: { nonItemsZero: { itemName: 'コピー先の日付', action: '選択' } }
      }),
      observer,
      submit,
      title
    }
  }
})
</script>
