<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-form-card
    v-if="syncedProgram.programIndex"
    :id="'weekly-services-card_' + syncedProgram.programIndex"
    data-z-ltcs-project-weekly-services-edit-card
    :title="`週間サービス計画表（No.${syncedProgram.programIndex}）`"
  >
    <template #header>
      <v-spacer />
      <v-tooltip top>
        <template #activator="{ on }">
          <v-btn
            color="secondary"
            data-copy-program
            icon
            text
            @click="onClickCopy(syncedProgram.programIndex)"
            v-on="on"
          >
            <v-icon>{{ $icons.copy }}</v-icon>
          </v-btn>
        </template>
        <span>コピー</span>
      </v-tooltip>
      <v-tooltip top>
        <template #activator="{ on }">
          <v-btn
            color="secondary"
            data-delete-program
            icon
            text
            @click="onClickDelete(syncedProgram.programIndex)"
            v-on="on"
          >
            <v-icon>{{ $icons.close }}</v-icon>
          </v-btn>
        </template>
        <span>削除</span>
      </v-tooltip>
    </template>
    <validation-observer v-slot="{ errors: observerErrors }" tag="div">
      <z-form-card-item-set :icon="$icons.category">
        <z-form-card-item
          v-slot="{ errors }"
          data-category
          vid="category"
          :rules="rules.category"
        >
          <z-select
            v-model="syncedProgram.category"
            label="サービス区分 *"
            :error-messages="errors"
            :items="serviceCategories"
            @change="onChangeCategory"
          />
        </z-form-card-item>
      </z-form-card-item-set>
      <z-form-card-item-set :icon="$icons.recurrence">
        <z-form-card-item
          v-slot="{ errors }"
          data-recurrence
          vid="recurrence"
          :rules="rules.recurrence"
        >
          <z-select
            v-model="syncedProgram.recurrence"
            label="繰り返し周期 *"
            :error-messages="errors"
            :items="recurrences"
          />
        </z-form-card-item>
      </z-form-card-item-set>
      <z-form-card-item-set :icon="$icons.dayOfWeek">
        <z-flex class="flex-wrap">
          <z-form-card-item
            v-for="dayOfWeek in dayOfWeeks"
            :key="dayOfWeek"
            :class="$style.day"
            vid="program.dayOfWeek"
            :rules="rules.dayOfWeek"
          >
            <v-checkbox
              v-model="syncedProgram.dayOfWeeks"
              class="mr-3"
              dense
              hide-details
              :label="resolveDayOfWeek(dayOfWeek)"
              :value="dayOfWeek"
            />
          </z-form-card-item>
        </z-flex>
      </z-form-card-item-set>
      <z-validate-error-messages
        v-slot="{ errors }"
        v-model="syncedProgram.dayOfWeeks"
        class="px-4 mb-2 mt-n2"
        data-program-day-of-week
        vid="dayOfWeek"
        :rules="rules.dayOfWeek"
      >
        <z-error-container
          v-if="!!errors.length"
        >
          <div class="error--text v-messages">
            {{ errors[0] }}
          </div>
        </z-error-container>
      </z-validate-error-messages>
      <z-form-card-item-set :icon="$icons.schedule">
        <z-flex>
          <z-form-card-item
            v-slot="{ errors }"
            data-program-slot-start
            vid="program.slot.start"
            :rules="rules.slot.start"
          >
            <z-text-field
              v-model="syncedProgram.slot.start"
              type="time"
              label="サービス開始 *"
              :error-messages="errors"
              @input="onChangeSlotStart"
            />
          </z-form-card-item>
          <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
          <z-form-card-item
            v-slot="{ errors }"
            data-program-slot-end
            vid="program.slot.end"
            :rules="rules.slot.end"
          >
            <z-text-field
              v-model="syncedProgram.slot.end"
              type="time"
              label="サービス終了 *"
              :error-messages="errors"
            />
          </z-form-card-item>
        </z-flex>
      </z-form-card-item-set>
      <z-data-card-item label="時間" :icon="$icons.timeAmount" :value="serviceElapsedMinute + '分'" />
      <z-form-card-item-set :icon="$icons.timeframe">
        <z-form-card-item
          v-slot="{ errors }"
          data-timeframe
          vid="timeframe"
          :rules="rules.timeframe"
        >
          <z-select
            v-model="syncedProgram.timeframe"
            label="算定時間帯 *"
            :error-messages="errors"
            :items="timeframe"
            @change="onChangeParams"
          />
        </z-form-card-item>
      </z-form-card-item-set>
      <z-form-card-item-set v-if="syncedProgram.category" label="サービス提供量" :icon="$icons.amount">
        <z-flex v-for="(x, i) in syncedProgram.amounts" :key="x.category">
          <div class="align-self-center mb-1">
            {{ resolveLtcsProjectAmountCategory(x.category) }}
          </div>
          <z-form-card-item
            :key="x.category"
            v-slot="{ errors }"
            data-amount
            :rules="rules.amount"
            :vid="'amount_' + i"
          >
            <z-text-field
              v-model.trim.number="x.amount"
              class="z-text-field--numeric"
              label="サービス時間 *"
              suffix="分"
              :error-messages="errors"
              @change="onChangeParams"
            />
          </z-form-card-item>
        </z-flex>
      </z-form-card-item-set>
      <z-error-container
        v-if="invalidAmountTime"
        class="mx-4"
      >
        <div class="error--text v-messages" data-invalid-amount-times>
          サービス時間の合計（{{ amountSum }}分）と時間（{{ serviceElapsedMinute }}分）を一致させてください。
        </div>
      </z-error-container>
      <z-form-card-item-set :icon="$icons.headcount">
        <z-form-card-item
          v-slot="{ errors }"
          data-headcount
          vid="headcount"
          :rules="rules.headcount"
        >
          <z-select
            v-model="syncedProgram.headcount"
            label="提供人数 *"
            :error-messages="errors"
            :items="[1, 2]"
            @change="onChangeParams"
          />
        </z-form-card-item>
      </z-form-card-item-set>
      <z-form-card-item-set v-if="isOwnExpense" :icon="$icons.ownExpenseProgram">
        <z-form-card-item
          v-slot="{ errors }"
          data-own-expense
          vid="ownExpense"
          :rules="rules.ownExpense"
        >
          <z-select
            v-model="syncedProgram.ownExpenseProgramId"
            label="自費サービス *"
            :error-messages="errors"
            :items="ownExpenseItems"
          />
        </z-form-card-item>
      </z-form-card-item-set>
      <z-form-card-item-set v-if="!isOwnExpense" :icon="$icons.serviceCode">
        <z-form-card-item v-slot="{ errors }" data-service-code vid="serviceCode" :rules="rules.serviceCode">
          <z-autocomplete
            v-model="serviceCodeModel"
            label="サービスコード *"
            return-object
            :error-messages="errors"
            :item-text="resolveServiceCode"
            :items="serviceCodeItems"
            :loading="serviceCodeLoading"
            :search-input.sync="serviceCodeInput"
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
              v-model="syncedProgram.options"
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
          <z-textarea v-model.trim="syncedProgram.note" label="備考" :error-messages="errors" />
        </z-form-card-item>
      </z-form-card-item-set>
      <z-error-container
        v-if="hasServiceMenuError(observerErrors)"
        class="mx-4"
      >
        <div
          v-if="hasMenuIdError(observerErrors)"
          class="error--text v-messages"
          data-empty-service-content
        >
          サービス内容を入力してください。
        </div>
        <div v-if="isInvalidTimes" class="error--text v-messages" data-invalid-duration-times>
          所要時間の合計（{{ contentsDurationSum }}分）と時間（{{ serviceElapsedMinute }}分）を一致させてください。
        </div>
      </z-error-container>
      <z-overflow-shadow>
        <v-data-table
          class="services-table"
          hide-default-footer
          mobile-breakpoint="0"
          :class="{'text-no-wrap': isMobile}"
          :dense="isMobile"
          :headers="headers"
          :items="syncedProgram.contents"
          :items-per-page="-1"
        >
          <template #body="{ items }">
            <tbody is="transition-group" :id="'dragTable' + syncedProgram.programIndex" name="card-list">
              <tr v-for="(item, i) in items" :key="contentKeys[i]" class="sortableRow" data-content-item>
                <td>
                  <span :class="$style.tableIcon">
                    <v-icon v-show="items.length > 1" small @click="deleteContent(i)">
                      {{ $icons.close }}
                    </v-icon>
                  </span>
                </td>
                <td>
                  <z-form-card-item
                    v-slot="{ errors }"
                    class="ml-0"
                    :class="$style.serviceMenu"
                    data-program-contents-menu-id
                    :rules="rules.contents.menuId"
                    :vid="'programContentsMenuId_' + contentKeys[i]"
                  >
                    <z-select
                      v-model="item.menuId"
                      label="サービス内容 *"
                      no-data-text="サービス項目を選択してください。"
                      :error="!!errors.length"
                      :items="serviceMenuOptions"
                    />
                  </z-form-card-item>
                </td>
                <td>
                  <z-form-card-item
                    v-slot="{ errors }"
                    class="ml-0"
                    data-program-contents-content
                    :rules="rules.contents.content"
                    :vid="'programContentsContent_' + contentKeys[i]"
                  >
                    <z-text-field v-model.trim="item.content" label="サービスの具体的内容" :error-messages="errors" />
                  </z-form-card-item>
                </td>
                <td>
                  <z-form-card-item
                    class="ml-0"
                    data-program-contents-duration
                    :rules="rules.contents.duration"
                    :vid="'programContentsDuration_' + contentKeys[i]"
                  >
                    <z-text-field
                      v-model.trim.number="item.duration"
                      class="z-text-field--numeric"
                      label="所要時間 *"
                      suffix="分"
                    />
                  </z-form-card-item>
                </td>
                <td>
                  <z-form-card-item
                    v-slot="{ errors }"
                    class="ml-0"
                    data-program-contents-memo
                    :rules="rules.contents.memo"
                    :vid="'programContentsMemo_' + contentKeys[i]"
                  >
                    <z-text-field v-model.trim="item.memo" label="留意事項" :error-messages="errors" />
                  </z-form-card-item>
                </td>
                <td>
                  <span :class="$style.tableIcon">
                    <v-icon
                      v-show="items.length > 1"
                      class="sortHandle table-icon"
                      :class="[$style.tableIcon, $style.handle]"
                      small
                    >
                      {{ $icons.sortable }}
                    </v-icon>
                  </span>
                </td>
              </tr>
            </tbody>
          </template>
        </v-data-table>
      </z-overflow-shadow>
      <div class="text-center mt-2">
        <v-btn block color="primary" data-add-content min-width="150" text @click="addContent">
          <v-icon left>{{ $icons.add }}</v-icon>
          <span>追加</span>
        </v-btn>
      </div>
    </validation-observer>
  </z-form-card>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { DayOfWeek, resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import {
  LtcsProjectAmountCategory,
  resolveLtcsProjectAmountCategory
} from '@zinger/enums/lib/ltcs-project-amount-category'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { Recurrence } from '@zinger/enums/lib/recurrence'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { isEmpty } from '@zinger/helpers'
import { Interval } from 'luxon'
// eslint-disable-next-line import/no-named-as-default
import Sortable from 'sortablejs'
import { createArrayWrapper } from '~/composables/create-array-wrapper'
import { createLtcsServiceOptions } from '~/composables/create-service-options'
import { appendHeadersCommonProperty } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { useInjected } from '~/composables/use-injected'
import { useLtcsProjectServiceMenuResolver } from '~/composables/use-ltcs-project-service-menu-resolver'
import { usePlugins } from '~/composables/use-plugins'
import { useServiceOptionItems } from '~/composables/use-service-option-items'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { DateLike, ISO_DATE_FORMAT, MINUTES_PER_DAY } from '~/models/date'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '~/models/ltcs-home-visit-long-term-care-dictionary-entry'
import { LtcsProjectAmount } from '~/models/ltcs-project-amount'
import { LtcsProjectContent } from '~/models/ltcs-project-content'
import { LtcsProjectProgram } from '~/models/ltcs-project-program'
import { OfficeId } from '~/models/office'
import { OwnExpenseProgramId } from '~/models/own-expense-program'
import { Range } from '~/models/range'
import { TimeDuration } from '~/models/time-duration'
import { $datetime } from '~/services/datetime-service'
import { updateReactiveArray } from '~/support/reactive'
import { numeric, required } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type Props = {
  effectivatedOn?: DateLike
  officeId?: OfficeId
  value: Overwrite<LtcsProjectProgram, {
    amounts: Partial<Writable<LtcsProjectAmount>>[]
    category: LtcsProjectServiceCategory
    contents: Partial<LtcsProjectContent>[]
    timeframe: Timeframe
    headcount: number
    ownExpenseProgramId: OwnExpenseProgramId | undefined
    serviceCode: string
  }>
}

export default defineComponent<Props>({
  name: 'ZLtcsProjectWeeklyServicesEditCard',
  props: {
    effectivatedOn: { type: [String, Object], default: undefined },
    officeId: { type: Number, default: undefined },
    value: { type: Object, required: true }
  },
  setup (props: Props, context) {
    const { $api } = usePlugins()
    const propRefs = toRefs(props)
    const { $vuetify } = usePlugins()
    const syncedValue = useSyncedProp('value', props, context, 'input')
    const contentsWrapper = createArrayWrapper(
      syncedValue.value.contents as DeepPartial<LtcsProjectContent[]>
    ) ?? []
    const useContents = () => ({
      contentKeys: contentsWrapper.keys,
      addContent: () => contentsWrapper.push({ menuId: undefined, duration: 0, content: '', memo: '' }),
      deleteContent: (index: number) => contentsWrapper.remove(index)
    })
    onMounted(() => {
      const tbody = document.querySelector<HTMLElement>('#dragTable' + syncedValue.value?.programIndex)
      if (!tbody) {
        return
      }
      Sortable.create(
        tbody,
        {
          draggable: '.sortableRow',
          handle: '.sortHandle',
          onEnd: ({ oldIndex, newIndex }) => {
            if (!isEmpty(oldIndex) && !isEmpty(newIndex)) {
              contentsWrapper.replace(oldIndex, newIndex)
            }
          }
        }
      )
    })
    const amountSum = computed(() => syncedValue.value.amounts?.reduce((amount, x) => amount + (x.amount || 0), 0))
    const contentsDurationSum = computed(() =>
      syncedValue.value.contents?.reduce((duration, x) => duration + (x.duration || 0), 0)
    )
    const { ownExpenseOptions, ownExpenseOptionsByOffice } = useInjected(ownExpenseProgramResolverStateKey)
    const ownExpenseItems = computed<Array<{ text: string, value: number }>>(() => {
      return isEmpty(props.officeId)
        ? ownExpenseOptions.value ?? []
        : ownExpenseOptionsByOffice.value(props.officeId) ?? []
    })
    watch(() => props.officeId, () => {
      if (
        syncedValue.value.ownExpenseProgramId &&
        !ownExpenseItems.value.some(x => x.value === syncedValue.value.ownExpenseProgramId)
      ) {
        syncedValue.value.ownExpenseProgramId = undefined
      }
    })
    const { getLtcsProjectServiceMenuOptions } = useLtcsProjectServiceMenuResolver()
    const serviceMenuOptions = computed(() => {
      const category = syncedValue.value.category
      return category ? getLtcsProjectServiceMenuOptions.value(category) : []
    })
    const isEmptySlotTimes = computed(() => {
      return isEmpty(syncedValue.value.slot.start) || isEmpty(syncedValue.value.slot.end)
    })
    const serviceElapsedMinute = computed(() => {
      if (isEmptySlotTimes.value) {
        return 0
      }
      return TimeDuration.diff(syncedValue.value.slot.start, syncedValue.value.slot.end).get.totalMinutes
    })
    const isElapsedTimeEqualsSum = computed(
      () => isEmptySlotTimes.value || serviceElapsedMinute.value === amountSum.value
    )
    const isInvalidTimes = computed(() => {
      return serviceElapsedMinute.value !== contentsDurationSum.value
    })
    const invalidAmountTime = computed(() => serviceElapsedMinute.value < amountSum.value)
    /**
     * validation-observerの特定の要素のエラーを返す.
     *
     * @param errors validation-observerのerrors
     * @param name validation-observerの要素の名前
     * @return string
     */
    const findObserversError = (errors: Record<string, string[]>, name: string) => {
      return Object.entries(errors).find(([key, value]) => {
        return key.startsWith(name) && !isEmpty(value[0])
      })?.[1][0]
    }
    /**
     * 所要時間のエラーがあればtrueを返す.
     *
     * @param errors validation-observerのerrors
     * @return boolean
     */
    const hasDurationsError = (errors: Record<string, string[]>) => {
      return !!findObserversError(errors, 'programContentsDuration')
    }
    /**
     * サービス内容のエラーがあればtrueを返す.
     *
     * @param errors validation-observerのerrors
     * @return boolean
     */
    const hasMenuIdError = (errors: Record<string, string[]>) => {
      return !!findObserversError(errors, 'programContentsMenuId')
    }
    /**
     * サービス詳細のエラーがあればtrueを返す.
     *
     * @param errors validation-observerのerrors
     * @return boolean
     */
    const hasServiceMenuError = (errors: Record<string, string[]>) => hasDurationsError(errors) ||
      isInvalidTimes.value ||
      hasMenuIdError(errors)
    /**
     * サービス区分に合わせてサービス提供量を更新する.
     *
     * @param category LtcsProjectServiceCategory
     */
    const onChangeCategory = async (category: LtcsProjectServiceCategory) => {
      if (category === LtcsProjectServiceCategory.physicalCareAndHousework) {
        updateReactiveArray(
          syncedValue.value.amounts,
          [{ category: LtcsProjectAmountCategory.physicalCare }, { category: LtcsProjectAmountCategory.housework }]
        )
      } else {
        updateReactiveArray(
          syncedValue.value.amounts,
          [{
            category: syncedValue.value.category as LtcsProjectAmountCategory,
            amount: serviceElapsedMinute.value || undefined
          }]
        )
      }
      await updateServiceCodeItems()
    }
    /**
     * サービス開始時間に合わせて、算定時間帯を設定する.
     *
     * @param time DateLike
     */
    const onChangeSlotStart = (time: DateLike) => {
      if (syncedValue.value.timeframe) {
        return
      }
      const checkIntervalContainsSlotStart = (start: string, end: string) => {
        return Interval.fromDateTimes($datetime.parse(start), $datetime.parse(end)).contains($datetime.parse(time))
      }
      if (checkIntervalContainsSlotStart('08:00', '18:00')) {
        syncedValue.value.timeframe = Timeframe.daytime
      } else if (checkIntervalContainsSlotStart('06:00', '08:00')) {
        syncedValue.value.timeframe = Timeframe.morning
      } else if (checkIntervalContainsSlotStart('18:00', '22:00')) {
        syncedValue.value.timeframe = Timeframe.night
      } else if (checkIntervalContainsSlotStart('22:00', '24:00') || checkIntervalContainsSlotStart('00:00', '06:00')) {
        syncedValue.value.timeframe = Timeframe.midnight
      }
    }
    const isOwnExpense = computed(() => syncedValue.value.category === LtcsProjectServiceCategory.ownExpense)
    const state = reactive({
      serviceCodeInput: '' as string | null | undefined,
      serviceCodeValue: undefined as LtcsHomeVisitLongTermCareDictionaryEntry | undefined,
      serviceCodeItems: undefined as LtcsHomeVisitLongTermCareDictionaryEntry[] | undefined,
      serviceCodeLoading: false
    })
    const stateRefs = toRefs(state)
    const getAmountMinutes = (category: LtcsProjectAmountCategory): number | undefined => {
      return syncedValue.value.amounts?.find(x => x.category === category)?.amount
    }
    const updateServiceCodeItems = async (q?: string) => {
      // サービス区分が「自費サービス」の時は何もしない
      if (syncedValue.value.category === LtcsProjectServiceCategory.ownExpense) {
        return
      }
      if (props.officeId !== undefined && props.effectivatedOn !== undefined && (q === undefined || q.match(/^[0-9A-Z]{1,6}$/))) {
        try {
          state.serviceCodeLoading = true
          // TODO: officeIdとisEffectiveOnが可変でもuseLtcsHomeVisitLongTermCareDictionaryを使えるようにする.
          const { list } = await $api.ltcsHomeVisitLongTermCareDictionary.getIndex({
            q,
            officeId: props.officeId,
            isEffectiveOn: $datetime.parse(props.effectivatedOn).toFormat(ISO_DATE_FORMAT),
            timeframe: syncedValue.value.timeframe,
            category: syncedValue.value.category,
            physicalMinutes: getAmountMinutes(LtcsProjectAmountCategory.physicalCare),
            houseworkMinutes: getAmountMinutes(LtcsProjectAmountCategory.housework),
            headcount: syncedValue.value.headcount
          })
          state.serviceCodeItems = list
          if (list.length === 1) {
            syncedValue.value.serviceCode = list[0].serviceCode
            state.serviceCodeValue = list[0]
          }
        } finally {
          state.serviceCodeLoading = false
        }
      }
    }
    const onChangeParams = async () => {
      // @change で `updateServiceCodeItems` を直接呼びだすと引数が指定されてしまうため間接的に呼びだす
      await updateServiceCodeItems()
    }
    watch(
      stateRefs.serviceCodeInput,
      async serviceCodeInput => {
        if (serviceCodeInput?.match(/^[0-9A-Z]{1,6}$/)) {
          await updateServiceCodeItems(serviceCodeInput)
        }
      }
    )
    watch(
      [propRefs.officeId, propRefs.effectivatedOn],
      async () => await updateServiceCodeItems(props.value.serviceCode),
      { immediate: true }
    )
    const completeCategory = (category: LtcsServiceCodeCategory) => {
      syncedValue.value.category = LtcsServiceCodeCategory.match<LtcsProjectServiceCategory>(category, {
        physicalCare: () => LtcsProjectServiceCategory.physicalCare,
        housework: () => LtcsProjectServiceCategory.housework,
        physicalCareAndHousework: () => LtcsProjectServiceCategory.physicalCareAndHousework,
        default: () => {
          throw new Error(`Unexpected category: ${category}`)
        }
      })
    }
    const completeAmount = (category: LtcsProjectServiceCategory, range: Range<number>) => {
      const index = syncedValue.value.amounts.findIndex(x => x.category === category)
      const isAmountAvailable = index >= 0
      const currentValue = isAmountAvailable ? syncedValue.value.amounts[index].amount : undefined
      const isValidAmountValue = currentValue !== undefined && currentValue > range.start && currentValue <= range.end
      if (isAmountAvailable && !isValidAmountValue) {
        syncedValue.value.amounts[index].amount = range.end
      }
    }
    const serviceCodeModel = computed({
      get: () => state.serviceCodeValue,
      set: (x: LtcsHomeVisitLongTermCareDictionaryEntry | undefined) => {
        if (x !== undefined) {
          syncedValue.value.serviceCode = x.serviceCode
          syncedValue.value.timeframe = x.timeframe
          syncedValue.value.headcount = x.headcount
          completeCategory(x.category)
          completeAmount(LtcsProjectServiceCategory.physicalCare, x.physicalMinutes)
          completeAmount(LtcsProjectServiceCategory.housework, x.houseworkMinutes)
        }
      }
    })
    const headers = appendHeadersCommonProperty([
      { text: '', value: 'actions', class: 'th-actions', width: 40 },
      { text: 'サービス内容', value: 'menuId', class: 'th-menu' },
      { text: 'サービスの具体的内容', value: 'content', width: 180 },
      { text: '所要時間', value: 'duration', width: 105 },
      { text: '留意事項', value: 'memo', class: 'th-memo', width: 180 },
      { text: '', value: 'handle', class: 'th-actions', width: 40 }
    ])
    const rules = computed<Rules>(() => {
      const customSlots = {
        message: 'サービス項目の合計時間と一致させてください。',
        validate: () => isElapsedTimeEqualsSum.value
      }
      const customTimes = {
        message: '',
        validate: () => !isInvalidTimes.value
      }
      const customServiceCode = {
        message: '入力してください。',
        validate: () => syncedValue.value.serviceCode !== undefined
      }
      return validationRules({
        amounts: { required },
        amount: { required, numeric, between: { min: 1, max: MINUTES_PER_DAY } },
        category: { required },
        recurrence: { required },
        slot: {
          start: { required },
          end: { required }
        },
        timeframe: { required },
        slots: { custom: customSlots },
        serviceCode: { required, custom: customServiceCode },
        headcount: { required },
        dayOfWeek: { required },
        ownExpense: { required },
        note: { max: 255 },
        contents: {
          menuId: { required },
          duration: { custom: customTimes, numeric, between: { min: 1, max: MINUTES_PER_DAY } },
          memo: { max: 255 }
        }
      })
    })
    return {
      ...useContents(),
      ...useServiceOptionItems(
        () => createLtcsServiceOptions('project', syncedValue.value.category),
        () => syncedValue.value.options.splice(0)
      ),
      ...stateRefs,
      amountSum,
      contentsDurationSum,
      dayOfWeeks: DayOfWeek.values,
      hasServiceMenuError,
      hasMenuIdError,
      headers,
      invalidAmountTime,
      isElapsedTimeEqualsSum,
      isInvalidTimes,
      isMobile: computed(() => $vuetify.breakpoint.mdAndDown),
      isOwnExpense,
      onChangeCategory,
      onChangeParams,
      onChangeSlotStart,
      onClickCopy: (index: number) => context.emit('click:copy', index),
      onClickDelete: (index: number) => context.emit('click:delete', index),
      ownExpenseItems,
      recurrences: enumerableOptions(Recurrence),
      resolveDayOfWeek,
      resolveLtcsProjectAmountCategory,
      resolveServiceCode: (x: LtcsHomeVisitLongTermCareDictionaryEntry): string => `${x.serviceCode}: ${x.name}`,
      rules,
      serviceCategories: enumerableOptions(LtcsProjectServiceCategory),
      serviceCodeModel,
      serviceElapsedMinute,
      serviceMenuOptions,
      state,
      syncedProgram: syncedValue,
      timeframe: enumerableOptions(Timeframe)
    }
  }
})
</script>

<style lang="scss" module>
@import '~vuetify/src/styles/styles';

.serviceMenu {
  width: 200px;
}

.tableIcon {
  min-width: 16px;
  display: inline-block;
}

.handle {
  cursor: move !important;
}

.day {
  flex: 1 1 10%;
}

@media #{map-get($display-breakpoints, 'sm-and-down')} {
  .day {
    flex: 1 1 25%;
  }
}
</style>
