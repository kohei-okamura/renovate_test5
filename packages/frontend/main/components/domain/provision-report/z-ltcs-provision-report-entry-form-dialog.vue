<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog max-width="640" persistent transition="dialog" :value="show" :width="width" @input="v => v || cancel()">
    <v-form data-form @submit.prevent="submit">
      <v-card>
        <z-card-titlebar color="blue-grey" data-title>
          <slot name="title"></slot>
        </z-card-titlebar>
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
          <z-form-card-item-set :icon="$icons.timeframe">
            <z-flex>
              <z-form-card-item
                v-slot="{ errors }"
                data-slot-start
                vid="slot.start"
                :rules="rules.slot.start"
              >
                <z-text-field
                  v-model="form.slot.start"
                  type="time"
                  label="サービス提供時間 *"
                  :error-messages="errors"
                />
              </z-form-card-item>
              <z-flex-shrink class="pb-2 pl-2 pt-4">〜</z-flex-shrink>
              <z-form-card-item v-slot="{ errors }" data-slot-end vid="slot.end" :rules="rules.slot.end">
                <z-text-field v-model="form.slot.end" type="time" :error-messages="errors" />
              </z-form-card-item>
            </z-flex>
            <v-row no-gutters>
              <v-col>
                <z-form-card-item v-slot="{ errors }" data-timeframe vid="timeframe" :rules="rules.timeframe">
                  <z-select
                    v-model="form.timeframe"
                    label="算定時間帯 *"
                    :error-messages="errors"
                    :items="timeframeItems"
                    @change="onChangeParams"
                  />
                </z-form-card-item>
              </v-col>
              <v-col v-for="(x, i) in form.amounts" :key="x.id">
                <z-form-card-item v-slot="{ errors }" data-amount :rules="rules.amount" :vid="`amount_${i}`">
                  <z-number-input-field
                    v-model="x.amount"
                    suffix="分"
                    :error-messages="errors"
                    :label="`${resolveLtcsProjectAmountCategory(x.category)} *`"
                    @change="onChangeParams"
                  />
                </z-form-card-item>
              </v-col>
            </v-row>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.headcount">
            <z-form-card-item v-slot="{ errors }" data-headcount vid="headcount" :rules="rules.headcount">
              <z-select
                v-model="form.headcount"
                label="提供人数 *"
                suffix="人"
                :error-messages="errors"
                :items="[1, 2]"
                @change="onChangeParams"
              />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set v-if="!hasOwnExpense" :icon="$icons.serviceCode">
            <z-form-card-item v-slot="{ errors }" data-service-code vid="serviceCode" :rules="rules.serviceCode">
              <z-autocomplete
                v-model="serviceCodeValue"
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
            <v-row class="px-4" justify="center" justify-md="end" no-gutters>
              <v-col cols="5" md="3">
                <v-btn data-cancel text width="100%" @click.stop="cancel">キャンセル</v-btn>
              </v-col>
              <v-col class="pl-4" cols="5" md="3">
                <v-btn color="primary" data-positive-label depressed type="submit" width="100%">
                  <slot name="positive-label"></slot>
                </v-btn>
              </v-col>
            </v-row>
          </z-form-card-item>
        </validation-observer>
      </v-card>
    </v-form>
  </v-dialog>
</template>

<script lang="ts">
import { computed, defineComponent, nextTick, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import {
  LtcsProjectAmountCategory,
  resolveLtcsProjectAmountCategory
} from '@zinger/enums/lib/ltcs-project-amount-category'
import { LtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { LtcsServiceCodeCategory } from '@zinger/enums/lib/ltcs-service-code-category'
import { Timeframe } from '@zinger/enums/lib/timeframe'
import { clone, debounce } from '@zinger/helpers'
import { createLtcsServiceOptions } from '~/composables/create-service-options'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useOwnExpenseProgramResolverStore } from '~/composables/stores/use-own-expense-program-resolver-store'
import { useLtcsHomeVisitLongTermCareDictionary } from '~/composables/use-ltcs-home-visit-long-term-care-dictionary'
import { useServiceOptionItems } from '~/composables/use-service-option-items'
import { DateLike, MINUTES_PER_DAY } from '~/models/date'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '~/models/ltcs-home-visit-long-term-care-dictionary-entry'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { OfficeId } from '~/models/office'
import { Range } from '~/models/range'
import { observerRef } from '~/support/reactive'
import { numeric, required } from '~/support/validation/rules'
import { validationRules } from '~/support/validation/utils'

type Form = Partial<Writable<LtcsProvisionReportEntry>>

type Props = {
  isEffectiveOn: DateLike
  officeId: OfficeId
  show: boolean
  value: {
    index: number | undefined
    entry: LtcsProvisionReportEntry
  }
  width: string | number
}

export default defineComponent<Props>({
  name: 'ZLtcsProvisionReportEntryFormDialog',
  props: {
    isEffectiveOn: { type: [String, Object], required: true },
    officeId: { type: Number, required: true },
    show: { type: Boolean, required: true },
    value: { type: Object, required: true },
    width: { type: String, required: true }
  },
  setup (props: Props, context) {
    const reactiveProps = toRefs(props)

    const useSelectOptions = () => {
      const { ownExpenseOptionsByOffice } = useOwnExpenseProgramResolverStore().state
      return ({
        timeframeItems: enumerableOptions(Timeframe),
        categoryItems: enumerableOptions(LtcsProjectServiceCategory),
        ownExpenseProgramItems: computed(() => ownExpenseOptionsByOffice.value(props.officeId) ?? [])
      })
    }

    const createFormValues = (values: Form = {}): Form => ({
      slot: {
        start: undefined,
        end: undefined
      },
      timeframe: undefined,
      category: undefined,
      amounts: [],
      headcount: undefined,
      ownExpenseProgramId: undefined,
      serviceCode: undefined,
      options: [],
      note: '',
      plans: [],
      results: [],
      ...clone(values)
    })
    const state = reactive({
      form: createFormValues(),
      serviceCodeInput: '' as string | null | undefined,
      serviceCodeLoading: false,
      serviceCodeItems: [] as LtcsHomeVisitLongTermCareDictionaryEntry[],
      serviceCodeValue: undefined as LtcsHomeVisitLongTermCareDictionaryEntry | undefined
    })
    const stateRefs = toRefs(state)
    const { searchLtcsHomeVisitLongTermCareDictionary } = useLtcsHomeVisitLongTermCareDictionary(
      props.officeId,
      props.isEffectiveOn
    )
    const completeTimeframe = (timeframe: Timeframe) => {
      state.form.timeframe = timeframe
    }
    const completeCategory = (category: LtcsServiceCodeCategory) => {
      state.form.category = LtcsServiceCodeCategory.match<LtcsProjectServiceCategory>(category, {
        physicalCare: () => LtcsProjectServiceCategory.physicalCare,
        housework: () => LtcsProjectServiceCategory.housework,
        physicalCareAndHousework: () => LtcsProjectServiceCategory.physicalCareAndHousework,
        default: () => {
          throw new Error(`Unexpected category: ${category}`)
        }
      })
    }
    const completeAmount = (category: LtcsProjectServiceCategory, range: Range<number>) => {
      if (state.form.amounts !== undefined) {
        const index = state.form.amounts.findIndex(x => x.category === category)
        const isAmountAvailable = index >= 0
        const currentValue = isAmountAvailable ? state.form.amounts[index].amount : undefined
        const isValidAmountValue = currentValue !== undefined && currentValue > range.start && currentValue <= range.end
        if (isAmountAvailable && !isValidAmountValue) {
          state.form.amounts[index].amount = range.end
        }
      }
    }
    const completeHeadcount = (headcount: number) => {
      state.form.headcount = headcount
    }
    const getTimeframe = (value: Timeframe | undefined) => {
      // 介護保険サービス：訪問介護のサービスコードでは早朝と夜間の区別がなく夜間に統一されていることに対応する
      return value === Timeframe.morning ? Timeframe.night : value
    }
    const getAmountMinutes = (category: LtcsProjectAmountCategory): number | undefined => {
      return state.form.amounts?.find(x => x.category === category)?.amount
    }
    const updateServiceCodeItems = debounce({ wait: 50 }, async (q?: string) => {
      // 閉じようとしているときは無駄に API を呼びださないようにする
      if (reactiveProps.show.value) {
        const formValues = state.form
        // サービス区分が「自費サービス」の時は何もしない
        if (formValues.category === LtcsProjectServiceCategory.ownExpense) {
          return
        }
        // 選択肢を選んだタイミングでラベルを含んだ値で検索が行われることを防止する
        if (q === undefined || q.match(/^[0-9A-Z]{1,6}$/)) {
          try {
            const params = {
              q,
              timeframe: getTimeframe(formValues.timeframe),
              category: formValues.category,
              physicalMinutes: getAmountMinutes(LtcsProjectAmountCategory.physicalCare),
              houseworkMinutes: getAmountMinutes(LtcsProjectAmountCategory.housework),
              headcount: formValues.headcount
            }
            state.serviceCodeLoading = true
            const items = await searchLtcsHomeVisitLongTermCareDictionary.value(params)
            state.serviceCodeItems = items
            if (items.length === 1) {
              state.serviceCodeValue = items[0]
            }
          } finally {
            state.serviceCodeLoading = false
          }
        }
      }
    })
    const onChangeParams = async () => {
      // @change で `updateServiceCodeItems` を直接呼びだすと引数が指定されてしまうため間接的に呼びだす
      await updateServiceCodeItems()
    }
    const resolveServiceCode = (x: LtcsHomeVisitLongTermCareDictionaryEntry): string => `${x.serviceCode}: ${x.name}`
    const cancel = () => context.emit('click:cancel')
    const observer = observerRef()
    const submit = async () => {
      if (await observer.value!.validate()) {
        context.emit('click:save', {
          index: reactiveProps.value.value.index,
          entry: clone(state.form)
        })
      }
    }
    const createAmount = (category: LtcsProjectAmountCategory) => ({
      category,
      amount: getAmountMinutes(category)
    })
    watch<Form['category']>(
      () => state.form.category,
      async category => {
        const Cat = LtcsProjectServiceCategory
        if (category !== Cat.ownExpense) {
          const containsPhysicalCare = category === Cat.physicalCare || category === Cat.physicalCareAndHousework
          const containsHousework = category === Cat.housework || category === Cat.physicalCareAndHousework
          state.form.amounts = [
            ...(containsPhysicalCare ? [createAmount(LtcsProjectAmountCategory.physicalCare)] : []),
            ...(containsHousework ? [createAmount(LtcsProjectAmountCategory.housework)] : [])
          ]
          state.form.ownExpenseProgramId = undefined
          await updateServiceCodeItems()
        } else {
          state.form.amounts = []
          state.form.serviceCode = undefined
        }
      }
    )
    watch(
      stateRefs.serviceCodeInput,
      async serviceCodeInput => {
        if (serviceCodeInput?.match(/^[0-9A-Z]{1,6}$/)) {
          await updateServiceCodeItems(serviceCodeInput)
        }
      }
    )
    watch(
      stateRefs.serviceCodeValue,
      entry => {
        if (entry !== undefined) {
          state.form.serviceCode = entry.serviceCode
          completeTimeframe(entry.timeframe)
          completeCategory(entry.category)
          completeHeadcount(entry.headcount)
          // サービス区分の変更が反映される前に以下の処理を行うと上書きされてしまい結局反映されない
          // サービス区分の変更が反映された後に処理を行うため遅延させる
          nextTick(() => {
            completeAmount(LtcsProjectServiceCategory.physicalCare, entry.physicalMinutes)
            completeAmount(LtcsProjectServiceCategory.housework, entry.houseworkMinutes)
          })
        }
      }
    )
    watch(
      reactiveProps.value,
      async value => {
        observer.value?.reset()
        state.form = createFormValues(value?.entry)
        state.serviceCodeInput = ''
        const serviceCode = value?.entry?.serviceCode
        if (serviceCode) {
          await updateServiceCodeItems(serviceCode)
        } else {
          state.serviceCodeItems = []
          state.serviceCodeValue = undefined
        }
      },
      { immediate: true }
    )

    const hasOwnExpense = computed(() => state.form.category === LtcsProjectServiceCategory.ownExpense)

    return {
      ...stateRefs,
      ...useSelectOptions(),
      ...useServiceOptionItems(
        () => createLtcsServiceOptions('provisionReport', state.form.category),
        () => state.form.options?.splice(0)
      ),
      cancel,
      hasOwnExpense,
      observer,
      onChangeParams,
      resolveLtcsProjectAmountCategory,
      resolveServiceCode,
      rules: validationRules({
        slot: {
          start: { required },
          end: { required }
        },
        timeframe: { required },
        category: { required },
        amount: { required, numeric, between: { min: 1, max: MINUTES_PER_DAY } },
        headcount: { required },
        ownExpenseProgramId: { required },
        serviceCode: { required },
        note: { max: 255 }
      }),
      submit
    }
  }
})
</script>

<style lang="scss" module>
.overlay {
  background-color: transparent;
  height: calc(100% - 76px);
  position: absolute;
  width: 100%;
  z-index: 1;
}
</style>
