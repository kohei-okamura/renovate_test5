<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog max-width="640" persistent transition="dialog" :value="show" :width="width" @input="v => v || cancel()">
    <v-form data-form @submit.prevent="submit">
      <v-card>
        <z-card-titlebar color="blue-grey">{{ title }}</z-card-titlebar>
        <validation-observer ref="observer" class="py-6" tag="div">
          <z-form-card-item-set :icon="$icons.category">
            <z-form-card-item v-slot="{ errors }" data-category vid="category" :rules="rules.category">
              <z-select
                v-model="form.category"
                label="サービス区分 *"
                :error-messages="errors"
                :items="categoryItems"
              />
            </z-form-card-item>
            <z-form-card-item
              v-if="hasOwnExpense"
              v-slot="{ errors }"
              data-own-expense-program-id
              vid="ownExpenseProgramId"
              :rules="rules.ownExpenseProgramId"
            >
              <z-select
                v-model="form.ownExpenseProgramId"
                label="自費サービス *"
                :error-messages="errors"
                :items="ownExpenseProgramItems"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set v-if="hasVisitingCareForPwsd" :icon="$icons.timeAmount">
            <z-form-card-item
              v-slot="{ errors }"
              data-moving-duration-minutes
              vid="movingDurationMinutes"
              :rules="rules.movingDurationMinutes"
            >
              <z-text-field
                v-model.trim="form.movingDurationMinutes"
                label="移動介護時間数"
                suffix="分"
                :error-messages="errors"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.schedule">
            <z-form-card-item
              v-slot="{ errors }"
              data-schedule-date
              vid="schedule.date"
              :rules="rules.schedule.date"
            >
              <z-date-field
                v-model="form.schedule.date"
                label="サービス提供年月日 *"
                :clearable="false"
                :error-messages="errors"
                :max="month.last"
                :min="month.first"
                :picker-date="month.month"
              />
            </z-form-card-item>
            <z-flex>
              <z-form-card-item
                v-slot="{ errors }"
                data-schedule-start
                vid="schedule.start"
                :rules="rules.schedule.start"
              >
                <z-text-field
                  v-model="form.schedule.start"
                  type="time"
                  label="サービス提供時間 *"
                  :error-messages="errors"
                />
              </z-form-card-item>
              <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
              <z-form-card-item
                v-slot="{ errors }"
                data-schedule-end
                vid="schedule.end"
                :rules="rules.schedule.end"
              >
                <z-text-field v-model="form.schedule.end" type="time" :error-messages="errors" />
              </z-form-card-item>
            </z-flex>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.headcount">
            <z-form-card-item v-slot="{ errors }" data-headcount vid="headcount" :rules="rules.headcount">
              <z-select
                v-model="form.headcount"
                label="提供人数 *"
                suffix="人"
                :error-messages="errors"
                :items="[1, 2]"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set
            v-if="hasOptionItems"
            :icon="$icons.serviceOption"
            class="pb-4"
            label="サービスオプション"
          >
            <template v-for="x in optionItems">
              <z-form-card-item v-if="x.enabled" :key="x.value">
                <v-checkbox
                  v-model="form.options"
                  persistent-hint
                  :hint="x.hint"
                  :label="x.text"
                  :value="x.value"
                />
              </z-form-card-item>
            </template>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.note">
            <z-form-card-item v-slot="{ errors }" data-note vid="note" :rules="rules.note">
              <z-textarea v-model.trim="form.note" label="備考" :error-messages="errors" />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item>
            <v-row class="pr-4" justify="center" justify-md="end" no-gutters>
              <v-col cols="5" md="3">
                <v-btn data-cancel text width="100%" @click.stop="cancel">キャンセル</v-btn>
              </v-col>
              <v-col class="pl-4" cols="5" md="3">
                <v-btn color="primary" data-ok depressed type="submit" width="100%">{{ actionText }}</v-btn>
              </v-col>
            </v-row>
          </z-form-card-item>
        </validation-observer>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script lang="ts">
import { computed, defineComponent, ref, watch } from '@nuxtjs/composition-api'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { assign, clone } from '@zinger/helpers'
import { createDwsServiceOptions } from '~/composables/create-service-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useOwnExpenseProgramResolverStore } from '~/composables/stores/use-own-expense-program-resolver-store'
import { time } from '~/composables/time'
import { useServiceOptionItems } from '~/composables/use-service-option-items'
import { DateLike, ISO_TIME_FORMAT } from '~/models/date'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'
import { OfficeId } from '~/models/office'
import { TimeRange } from '~/models/range'
import { scheduleFromTimeRange } from '~/models/schedule'
import { $datetime } from '~/services/datetime-service'
import { observerRef } from '~/support/reactive'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Form = Overwrite<Writable<DwsProvisionReportItem>, {
  schedule: Partial<TimeRange> & {
    date: DateLike | undefined
  }
  movingDurationMinutes?: number | string
}>

type Props = {
  officeId: OfficeId
  providedIn: DateLike
  show: boolean
  target: string
  value: {
    key: string | undefined
    item: DwsProvisionReportItem | undefined
  }
  width: string
}

export default defineComponent<Props>({
  name: 'ZDwsProvisionReportItemFormDialog',
  props: {
    officeId: { type: Number, required: true },
    providedIn: { type: [String, Object], required: true },
    show: { type: Boolean, required: true },
    target: { type: String, required: true },
    value: { type: Object, required: true },
    width: { type: String, required: true }
  },
  setup (props: Props, context) {
    const providedIn = $datetime.parse(props.providedIn)
    const month = {
      month: providedIn.toISODate(),
      first: providedIn.startOf('month').toISODate(),
      last: providedIn.endOf('month').toISODate()
    }
    const { ownExpenseOptionsByOffice } = useOwnExpenseProgramResolverStore().state
    const useSelectOptions = () => ({
      categoryItems: enumerableOptions(DwsProjectServiceCategory),
      ownExpenseProgramItems: computed(() => ownExpenseOptionsByOffice.value(props.officeId) ?? [])
    })

    const createEmptyForm = (): Form => ({
      schedule: {
        date: undefined,
        start: undefined,
        end: undefined
      },
      category: undefined,
      headcount: undefined,
      movingDurationMinutes: undefined,
      ownExpenseProgramId: undefined,
      options: [],
      note: undefined
    })
    const createForm = (item: DwsProvisionReportItem | undefined): Form => {
      const copied = clone(item)
      return copied === undefined
        ? createEmptyForm()
        : {
          ...copied,
          schedule: {
            date: copied.schedule.date,
            start: time(copied.schedule.start, ISO_TIME_FORMAT),
            end: time(copied.schedule.end, ISO_TIME_FORMAT)
          }
        }
    }
    const form = ref<Form>({
      ...createEmptyForm(),
      ...createForm(props.value.item)
    })
    const observer = observerRef()
    const resetError = () => observer.value?.reset()
    watch(() => props.value.item, item => {
      resetError()
      form.value = {
        ...createEmptyForm(),
        ...createForm(item)
      }
    })
    watch<Form['category']>(
      () => form.value.category,
      category => {
        if (category !== DwsProjectServiceCategory.ownExpense) {
          form.value.ownExpenseProgramId = undefined
        }
      }
    )
    const actionText = computed(() => props.value.item?.schedule.date ? '編集' : '追加')
    const title = computed(() => `${props.target}を${actionText.value}`)
    const hasOwnExpense = computed(() => form.value.category === DwsProjectServiceCategory.ownExpense)
    const hasVisitingCareForPwsd = computed(() => form.value.category === DwsProjectServiceCategory.visitingCareForPwsd)
    const submit = async () => {
      if (await observer.value?.validate()) {
        const item = clone(form.value)
        /*
         * 入力値を加工する
         * - 開始・終了を日付型（yyyy-MM-dd'T'HH:mm:ssZZZ）にする
         * - 移動介護時間数が未入力の場合は 0 にする
         */
        assign(item, {
          schedule: scheduleFromTimeRange(item.schedule),
          movingDurationMinutes: hasVisitingCareForPwsd.value ? +(item.movingDurationMinutes ?? 0) : 0
        })
        context.emit('click:save', { key: props.value.key, item })
      }
    }
    const cancel = () => {
      context.emit('click:cancel')
      resetError()
      form.value = createEmptyForm()
    }
    return {
      ...useSelectOptions(),
      ...useServiceOptionItems(
        () => createDwsServiceOptions('provisionReport', form.value.category),
        () => form.value.options?.splice(0)
      ),
      actionText,
      cancel,
      form,
      hasOwnExpense,
      hasVisitingCareForPwsd,
      month,
      rules: validationRules({
        schedule: {
          date: { required },
          start: { required },
          end: { required }
        },
        category: { required },
        headcount: { required },
        movingDurationMinutes: { numeric },
        ownExpenseProgramId: { required },
        note: { max: 255 }
      }),
      observer,
      submit,
      title
    }
  }
})
</script>
