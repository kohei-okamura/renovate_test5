<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-form-card
    v-if="syncedProgram.summaryIndex"
    :id="'weekly-services-card_' + syncedProgram.summaryIndex"
    data-z-project-weekly-services-edit-card
    :title="`週間サービス計画表（No.${syncedProgram.summaryIndex}）`"
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
            @click="onClickCopy(syncedProgram.summaryIndex)"
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
            @click="onClickDelete(syncedProgram.summaryIndex)"
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
      <z-form-card-item-set :icon="$icons.schedule">
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
      <z-form-card-item-set>
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
        <div v-if="isInvalidTimes" class="error--text v-messages" data-invalid-times>
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
            <tbody is="transition-group" :id="'dragTable' + syncedProgram.summaryIndex" name="card-list">
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
                      :items="filteredServiceMenuOptions"
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
                    <z-text-field v-model.trim="item.content" label="サービスの具体的内容 *" :error-messages="errors" />
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
                      :error="hasDurationsError(observerErrors)"
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
                    <v-icon v-show="items.length > 1" class="sortHandle handle" :class="$style.tableIcon" small>
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
          <span>サービス詳細を追加</span>
        </v-btn>
      </div>
    </validation-observer>
  </z-form-card>
</template>

<script lang="ts">
import { computed, defineComponent, onMounted, watch } from '@nuxtjs/composition-api'
import { DayOfWeek, resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import { DwsProjectServiceCategory } from '@zinger/enums/lib/dws-project-service-category'
import { Recurrence } from '@zinger/enums/lib/recurrence'
import { ServiceOption } from '@zinger/enums/lib/service-option'
import { isEmpty, nonEmpty } from '@zinger/helpers'
// eslint-disable-next-line import/no-named-as-default
import Sortable from 'sortablejs'
import { createArrayWrapper } from '~/composables/create-array-wrapper'
import { createDwsServiceOptions } from '~/composables/create-service-options'
import { appendHeadersCommonProperty } from '~/composables/data-table-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { ownExpenseProgramResolverStateKey } from '~/composables/stores/use-own-expense-program-resolver-store'
import { useDwsProjectServiceMenuResolver } from '~/composables/use-dws-project-service-menu-resolver'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useServiceOptionItems } from '~/composables/use-service-option-items'
import { useSyncedProp } from '~/composables/use-synced-prop'
import { MINUTES_PER_DAY } from '~/models/date'
import { DwsProjectContent } from '~/models/dws-project-content'
import { DwsProjectProgram } from '~/models/dws-project-program'
import { OfficeId } from '~/models/office'
import { OwnExpenseProgramId } from '~/models/own-expense-program'
import { $datetime } from '~/services/datetime-service'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Props = {
  value: Overwrite<DwsProjectProgram, {
    ownExpenseProgramId: OwnExpenseProgramId | undefined
  }>
  officeId?: OfficeId
}

export default defineComponent<Props>({
  name: 'ZDwsProjectWeeklyServicesEditCard',
  props: {
    value: { type: Object, required: true },
    officeId: { type: Number, default: undefined }
  },
  setup (props, context) {
    const { $vuetify } = usePlugins()
    const syncedValue = useSyncedProp('value', props, context, 'input')
    const contentsWrapper = createArrayWrapper(
      syncedValue.value.contents as DeepPartial<DwsProjectContent[]>
    ) ?? []
    const useContents = () => ({
      contentKeys: contentsWrapper.keys,
      addContent: () => contentsWrapper.push({ menuId: undefined, duration: 0, content: '', memo: '' }),
      deleteContent: (index: number) => contentsWrapper.remove(index)
    })
    onMounted(() => {
      const tbody = document.querySelector<HTMLElement>('#dragTable' + syncedValue.value.summaryIndex)
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
    const contentsDurationSum = computed(() =>
      syncedValue.value.contents.reduce((duration, x) => duration + (x.duration || 0), 0)
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
    const { getDwsProjectServiceMenuOptions } = useDwsProjectServiceMenuResolver()
    const filteredServiceMenuOptions = computed(() => {
      const category = syncedValue.value.category
      return category ? getDwsProjectServiceMenuOptions.value(category) : []
    })
    const isEmptySlotTimes = computed(() => {
      return isEmpty(syncedValue.value.slot.start) || isEmpty(syncedValue.value.slot.end)
    })
    const serviceElapsedMinute = computed(() => {
      if (isEmptySlotTimes.value) {
        return 0
      }
      const start = $datetime.parse(syncedValue.value.slot.start)
      const end = $datetime.parse(syncedValue.value.slot.end)
      const time = end.diff(start, 'minutes').minutes
      return time < 0 ? time + MINUTES_PER_DAY : time
    })
    const isInvalidTimes = computed(() => serviceElapsedMinute.value !== contentsDurationSum.value)
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
    const hasServiceMenuError = (errors: Record<string, string[]>) => {
      return isInvalidTimes.value ||
        hasMenuIdError(errors)
    }
    const isOwnExpense = computed(() => syncedValue.value.category === DwsProjectServiceCategory.ownExpense)
    /**
     * サービス区分が自費サービス以外になった時に自費サービスがあれば空にする.
     */
    const onChangeCategory = () => {
      if (nonEmpty(syncedValue.value.ownExpenseProgramId) && isOwnExpense.value) {
        syncedValue.value = {
          ...syncedValue.value,
          ownExpenseProgramId: undefined
        }
      }
    }
    const headers = appendHeadersCommonProperty([
      { text: '', value: 'actions', class: 'th-actions', width: 40 },
      { text: 'サービス内容', value: 'menuId', class: 'th-menu' },
      { text: 'サービスの具体的内容', value: 'content', width: 180 },
      { text: '所要時間', value: 'duration', width: 105 },
      { text: '留意事項', value: 'memo', width: 180 },
      { text: '', value: 'actions', class: 'th-actions', width: 40 }
    ])
    const rules = computed(() => {
      const customTimes = {
        message: '',
        validate: () => !isInvalidTimes.value
      }
      return validationRules({
        category: { required },
        contents: {
          menuId: { required },
          duration: { custom: customTimes, numeric, between: { min: 1, max: MINUTES_PER_DAY } },
          content: { required, max: 255 },
          memo: { max: 255 }
        },
        dayOfWeek: { required },
        headcount: { required, numeric },
        note: { max: 255 },
        recurrence: { required },
        ownExpense: { required },
        slot: { start: { required }, end: { required } }
      })
    })
    return {
      ...useContents(),
      ...useServiceOptionItems(
        () => createDwsServiceOptions('project', syncedValue.value.category),
        () => syncedValue.value.options.splice(0)
      ),
      contentsDurationSum,
      dayOfWeeks: DayOfWeek.values,
      filteredServiceMenuOptions,
      findObserversError,
      hasDurationsError,
      hasServiceMenuError,
      hasMenuIdError,
      headers,
      isInvalidTimes,
      isMobile: computed(() => $vuetify.breakpoint.mdAndDown),
      isOwnExpense,
      onChangeCategory,
      onClickCopy: (index: number) => context.emit('click:copy', index),
      onClickDelete: (index: number) => context.emit('click:delete', index),
      ownExpenseItems,
      recurrences: enumerableOptions(Recurrence),
      resolveDayOfWeek,
      serviceCategories: enumerableOptions(DwsProjectServiceCategory),
      serviceElapsedMinute,
      serviceOptions: enumerableOptions(ServiceOption),
      syncedProgram: syncedValue,
      rules
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
