<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-z-dws-billing-copay-coordination-form :class="$style.root" data-form @submit.prevent="submit">
    <z-data-card title="基本情報">
      <z-data-card-item label="市町村番号" :icon="$icons.office" :value="bundle.cityCode" />
      <z-data-card-item label="サービス提供年月" :icon="$icons.date">
        <z-era-date :value="bundle.providedIn" />
      </z-data-card-item>
      <z-data-card-item
        label="利用者負担上限額管理結果票の状態"
        :icon="statusIcon"
        :value="resolveDwsBillingStatus(status)"
      />
    </z-data-card>
    <z-data-card title="受給者情報">
      <z-data-card-item label="受給者証番号" :icon="$icons.dwsNumber" :value="user.dwsNumber" />
      <z-data-card-item label="支給決定障害者等氏名" :icon="$icons.dws" :value="user.name.displayName" />
      <z-data-card-item label="支給決定に係る障害児氏名" :value="user.childName.displayName" />
      <z-data-card-item label="利用者負担上限月額" :icon="$icons.copayLimit">
        {{ numeral(user.copayLimit) }}円
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="管理事業者">
      <z-data-card-item label="指定事業所番号" :icon="$icons.office" :value="office.code" />
      <z-data-card-item label="事業者及びその事業所の名称" :value="office.name" />
    </z-data-card>
    <validation-observer ref="observer" v-slot="observerProps" tag="div">
      <z-form-card title="サービス提供状況">
        <z-form-card-item-set label="他事業所におけるサービス提供有無" :no-icon="isMobile">
          <z-form-card-item
            v-slot="{ errors }"
            data-is-provided
            vid="isProvided"
            :rules="rules.isProvided"
          >
            <v-radio-group
              v-model="serviceProvisionStatus.isProvided.value"
              :error-messages="errors"
            >
              <v-row>
                <v-col
                  v-for="x in serviceProvisionStatus.options"
                  :key="x.value"
                  cols="12"
                  sm="5"
                  md="4"
                >
                  <v-radio :label="x.text" :value="x.value" />
                </v-col>
              </v-row>
            </v-radio-group>
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="作成区分">
        <z-form-card-item-set
          label="作成区分"
          :icon="$icons.category"
        >
          <z-form-card-item
            v-slot="{ errors }"
            data-exchange-aim
            vid="exchangeAim"
            :rules="rules.exchangeAim"
          >
            <v-radio-group
              v-model="exchangeAim.current.value"
              :error-messages="errors"
            >
              <v-row>
                <template v-for="x in exchangeAim.options">
                  <v-col
                    :key="x.value"
                    cols="12"
                    sm="2"
                  >
                    <v-radio :label="x.text" :value="x.value" />
                  </v-col>
                </template>
              </v-row>
            </v-radio-group>
            <div class="v-messages">過誤請求の場合のみ「修正」を選択してください。</div>
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="利用者負担上限額管理結果">
        <z-form-card-item-set :no-icon="isMobile">
          <z-form-card-item
            v-slot="{ errors }"
            data-result
            vid="result"
            :rules="rules.result"
          >
            <v-radio-group
              v-model="result"
              :error-messages="errors"
            >
              <v-row v-for="x in resultTypes" :key="x.value">
                <v-col>
                  <v-radio :label="x.text" :value="x.value" />
                </v-col>
              </v-row>
            </v-radio-group>
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-card title="利用者負担額集計・調整欄">
        <z-error-container
          v-show="hasObserverError(observerProps, ['numberOfOffices', 'officeOnly', 'total.coordinatedCopay'])"
          class="mx-4 mb-2"
        >
          <z-validate-error-messages
            data-number-of-offices
            vid="numberOfOffices"
            :rules="rules.numberOfOffices"
            :value="items.length"
          />
          <z-validate-error-messages
            data-office-only
            vid="officeOnly"
            :rules="rules.officeOnly"
            :value="items.length"
          />
          <z-validate-error-messages
            data-total-coordinated-copay
            vid="total.coordinatedCopay"
            :rules="rules.total.coordinatedCopay"
            :value="totalPayment.coordinatedCopay"
          />
        </z-error-container>
        <v-simple-table class="item-table" dense>
          <template #default>
            <thead>
              <tr>
                <th style="width: 40px"></th>
                <th class="text-center" :class="$style.writingVertical" style="width: 42px">項番</th>
                <th>事業所名</th>
                <th class="text-right">総費用額</th>
                <th class="text-right">利用者負担額</th>
                <th class="text-right">管理結果後<br>利用者負担額</th>
              </tr>
            </thead>
            <validation-observer
              v-for="(x, i) in items"
              :key="`tbody_${i}`"
              :ref="`childObserver${i}`"
              v-slot="childObserverProps"
              tag="tbody"
            >
              <tr class="no-border">
                <td class="text-center">
                  <v-btn v-if="x.deletable" color="danger" data-delete-item-btn icon small @click="deleteItem(i)">
                    <v-icon>{{ $icons.close }}</v-icon>
                  </v-btn>
                </td>
                <td class="text-center">{{ i + 1 }}</td>
                <td>
                  <z-form-card-item
                    v-slot="{ errors }"
                    class="ml-1"
                    data-office
                    vid="officeId"
                    :custom-messages="customErrorMessages.officeId"
                    :rules="rules.officeId"
                  >
                    <z-keyword-filter-autocomplete
                      v-model="x.officeId.value"
                      label="事業所 *"
                      style="width: 210px"
                      :disabled="x.disabled.officeId"
                      :error="isNonEmptyArray(errors)"
                      :items="x.officeOptions.value"
                      :loading="isLoadingOffices"
                    />
                  </z-form-card-item>
                </td>
                <td>
                  <z-form-card-item
                    v-slot="{ errors }"
                    class="ml-1"
                    data-subtotal-fee
                    :custom-messages="customErrorMessages.fee"
                    :rules="rules.items[i].subtotal.fee"
                    :vid="`items.${i}.subtotal.fee`"
                  >
                    <z-text-field
                      v-model="x.subtotal.fee.value"
                      style="width: 108px"
                      suffix="円"
                      type="number"
                      :error="isNonEmptyArray(errors)"
                      :min="0"
                    />
                  </z-form-card-item>
                </td>
                <td>
                  <z-form-card-item
                    v-slot="{ errors }"
                    class="ml-1"
                    data-subtotal-copay
                    :custom-messages="customErrorMessages.copay"
                    :rules="rules.items[i].subtotal.copay"
                    :vid="`items.${i}.subtotal.copay`"
                  >
                    <z-text-field
                      v-model="x.subtotal.copay.value"
                      style="width: 108px"
                      suffix="円"
                      type="number"
                      :error="isNonEmptyArray(errors)"
                      :min="0"
                    />
                  </z-form-card-item>
                </td>
                <td>
                  <z-form-card-item
                    v-slot="{ errors }"
                    class="ml-1"
                    data-subtotal-coordinated-copay
                    :custom-messages="customErrorMessages.coordinatedCopay"
                    :rules="rules.items[i].subtotal.coordinatedCopay"
                    :vid="`items.${i}.subtotal.coordinatedCopay`"
                  >
                    <z-text-field
                      v-model="x.subtotal.coordinatedCopay.value"
                      style="width: 108px"
                      suffix="円"
                      type="number"
                      :disabled="x.disabled.coordinatedCopay.value"
                      :error="isNonEmptyArray(errors)"
                      :min="0"
                    />
                  </z-form-card-item>
                </td>
              </tr>
              <tr v-if="hasObserverError(childObserverProps)">
                <td colspan="2"></td>
                <td colspan="4">
                  <ul :data-error-messages="i" class="pl-3 error--text" :class="$style.noBullet">
                    <li v-for="(v, j) in getErrorMessages(childObserverProps)" :key="`error_${j}`" class="v-messages">
                      <span>{{ v }}</span>
                    </li>
                  </ul>
                </td>
              </tr>
            </validation-observer>
            <tbody>
              <tr class="total">
                <td colspan="3"></td>
                <td class="text-right">{{ numeral(totalPayment.fee) }}円</td>
                <td class="text-right">{{ numeral(totalPayment.copay) }}円</td>
                <td class="text-right">{{ numeral(totalPayment.coordinatedCopay) }}円</td>
              </tr>
            </tbody>
          </template>
        </v-simple-table>
      </z-form-card>
      <v-card v-if="serviceProvisionStatus.isProvided.value" class="mt-3">
        <v-card-text class="pa-0">
          <v-btn block class="text-button pa-0" color="primary" data-add-item-btn text x-large @click="addItem">
            <v-icon left>{{ $icons.add }}</v-icon>
            <span>利用者負担額集計・調整欄を追加</span>
          </v-btn>
        </v-card-text>
      </v-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, ComputedRef, defineComponent, Ref, ref, watch, WritableComputedRef } from '@nuxtjs/composition-api'
import { CopayCoordinationResult } from '@zinger/enums/lib/copay-coordination-result'
import { DwsBillingCopayCoordinationExchangeAim } from '@zinger/enums/lib/dws-billing-copay-coordination-exchange-aim'
import { DwsBillingStatus, resolveDwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import deepmerge from 'deepmerge'
import { enumerableOptions } from '~/composables/enumerable-options'
import { numeral } from '~/composables/numeral'
import { useDwsBillingStatusIcon } from '~/composables/use-dws-billing-status-icon'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { OfficeOption, useOffices } from '~/composables/use-offices'
import { usePlugins } from '~/composables/use-plugins'
import { DwsBillingBundle } from '~/models/dws-billing-bundle'
import { DwsBillingCopayCoordinationPayment } from '~/models/dws-billing-copay-coordination-payment'
import { DwsBillingOffice } from '~/models/dws-billing-office'
import { DwsBillingStatement } from '~/models/dws-billing-statement'
import { OfficeId } from '~/models/office'
import { DwsBillingCopayCoordinationsApi } from '~/services/api/dws-billing-copay-coordinations-api'
import { RefOrValue } from '~/support/reactive'
import { numeric, required } from '~/support/validation/rules'
import { Rules, ValidationObserverInstance } from '~/support/validation/types'
import { validationRules } from '~/support/validation/utils'

type LocalSubtotal = Partial<{
  fee: DwsBillingCopayCoordinationsApi.Subtotal['fee'] | string
  copay: DwsBillingCopayCoordinationsApi.Subtotal['copay'] | string
  coordinatedCopay: DwsBillingCopayCoordinationsApi.Subtotal['coordinatedCopay'] | string
}>
type PartialItem = {
  officeId?: OfficeId
  subtotal: {
    fee: LocalSubtotal['fee']
    copay: LocalSubtotal['copay']
    coordinatedCopay: LocalSubtotal['coordinatedCopay']
  }
}
type ListItem = {
  deletable: boolean
  disabled: {
    coordinatedCopay: RefOrValue<boolean>
    officeId: boolean
  }
  officeId: WritableComputedRef<string | number | undefined>
  officeOptions: Ref<OfficeOption[]>
  subtotal: {
    fee: WritableComputedRef<LocalSubtotal['copay']>
    copay: WritableComputedRef<LocalSubtotal['copay']>
    coordinatedCopay: WritableComputedRef<LocalSubtotal['copay']>
  }
}

type Props = FormProps<DwsBillingCopayCoordinationsApi.Form> & Readonly<{
  buttonText: string
  bundle: DwsBillingBundle
  office: DwsBillingOffice
  statement: DwsBillingStatement
  status: DwsBillingStatus
}>

export default defineComponent<Props>({
  name: 'ZDwsBillingCopayCoordinationForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    bundle: { type: Object, required: true },
    office: { type: Object, required: true },
    statement: { type: Object, required: true },
    status: { type: Number, required: true }
  },
  setup (props, context) {
    const { $vuetify } = usePlugins()
    const { form, observer, submit } = useFormBindings(props, context, {
      init: form => ({
        exchangeAim: form.exchangeAim ?? DwsBillingCopayCoordinationExchangeAim.declaration
      }),
      processOutput: _ => ({
        userId: props.statement.user.userId,
        items: items.value,
        exchangeAim: exchangeAim.current.value,
        result: currentResult.value,
        isProvided: serviceProvisionStatus.isProvided.value
      })
    })
    const { isLoadingOffices, officeOptions } = useOffices({
      permission: Permission.updateBillings,
      qualifications: [
        OfficeQualification.dwsHomeHelpService,
        OfficeQualification.dwsVisitingCareForPwsd,
        OfficeQualification.dwsCommAccompany,
        OfficeQualification.dwsOthers
      ]
    })
    const filteredOfficeOptions = computed(() => officeOptions.value.filter(x => x.value !== props.office.officeId))
    const useServiceProvisionStatus = () => {
      // 作成時は初期値「提供あり（true）」
      // 編集時は調整欄の行数が1行だったら初期値「提供なし（false）」そうでなければ「提供あり（true）」
      const isProvided = ref(
        Object.keys(props.value).length === 0 // 作成の場合は props.value が {} になる
          ? true
          : (props.value.items ?? []).length > 1
      )
      const options = [
        { text: 'サービス提供あり', value: true },
        { text: 'サービス提供なし', value: false }
      ]
      return {
        isProvided,
        options
      }
    }
    const useExchangeAim = () => {
      const current: Ref<DwsBillingCopayCoordinationExchangeAim | undefined> = ref(form.exchangeAim)
      const options = enumerableOptions(DwsBillingCopayCoordinationExchangeAim)
        .filter(x => x.value !== DwsBillingCopayCoordinationExchangeAim.cancel)
      return {
        current,
        options
      }
    }
    const resultTypes = enumerableOptions(CopayCoordinationResult)
    const currentResult: Ref<CopayCoordinationResult | undefined> = ref(form.result)
    const isAppropriated = computed(() => currentResult.value === CopayCoordinationResult.appropriated)
    const isNotCoordinated = computed(() => currentResult.value === CopayCoordinationResult.notCoordinated)
    const toInt = (value?: string | number) => {
      if (!value) {
        return 0
      } else if (typeof value === 'number') {
        return value
      } else {
        return parseInt(value) || 0
      }
    }
    // 自事業所（管理事業所）
    const createNewItem = ({ officeId, subtotal }: DeepPartial<PartialItem> = {}) => ({
      officeId,
      subtotal: {
        fee: subtotal?.fee,
        copay: subtotal?.copay,
        coordinatedCopay: subtotal?.coordinatedCopay
      }
    })
    const items: Ref<PartialItem[]> = ref(form.items && form.items.length >= 1
      ? [...form.items]
      : [
        // 総費用額：明細書の「請求額集計欄：合計：総費用額」
        // 利用者負担額：明細書の「請求額集計欄：合計：上限月額調整」
        // 管理結果後利用者負担額：同上
        createNewItem({
          officeId: props.office.officeId,
          subtotal: {
            fee: props.statement.totalFee,
            copay: props.statement.totalCappedCopay,
            coordinatedCopay: props.statement.totalCappedCopay
          }
        }),
        createNewItem({ subtotal: { coordinatedCopay: isAppropriated.value ? 0 : undefined } })
      ]
    )
    const addItem = () => {
      items.value.push(createNewItem({
        subtotal: { coordinatedCopay: isAppropriated.value ? 0 : undefined }
      }))
    }
    const deleteItem = (index: number) => {
      items.value.splice(index, 1)
    }
    const totalPayment: ComputedRef<DwsBillingCopayCoordinationPayment> = computed(() => {
      return items.value.reduce((acc, { subtotal }) => {
        return {
          fee: acc.fee + toInt(subtotal.fee),
          copay: acc.copay + toInt(subtotal.copay),
          coordinatedCopay: acc.coordinatedCopay + toInt(subtotal.coordinatedCopay)
        }
      }, { fee: 0, copay: 0, coordinatedCopay: 0 })
    })
    watch(currentResult, value => {
      observer.value?.reset()
      switch (value) {
        case CopayCoordinationResult.appropriated:
          // 1 件目の 管理結果後利用者負担額 を 利用者負担上限月額 にする
          // 2 件目以降の 管理結果後利用者負担額 を 0 にする
          items.value = [
            deepmerge(items.value[0], { subtotal: { coordinatedCopay: props.statement.user.copayLimit } }),
            ...items.value.slice(1).map(x => deepmerge(x, { subtotal: { coordinatedCopay: 0 } }))
          ]
          break
        case CopayCoordinationResult.notCoordinated:
          // 管理結果後利用者負担額 を 利用者負担額 にする
          items.value = items.value.map(x => deepmerge(x, { subtotal: { coordinatedCopay: x.subtotal.copay } }))
          break
        case CopayCoordinationResult.coordinated:
          // 管理結果後利用者負担額 を 空欄 にする
          items.value = items.value.map(x => deepmerge(x, { subtotal: { coordinatedCopay: undefined } }))
          break
      }
    })
    const useConditionalListItems = () => {
      /*
       * 管理事業所で利用者負担額を充当したため、他事業所の利用者負担は発生しない。(appropriated)
       *   1行目
       *     事業所名: { 値: 管理事業所（自事業所）を設定, 変更: 不可 }
       *     総費用額: { 値: 明細書の値から自動入力したい, 変更: 可 }
       *     利用者負担額: { 値: 明細書の値から自動入力したい, 変更: 可 }
       *     管理結果後利用者負担額: { 値: 利用者負担上限月額の値を設定, 変更: 不可 }
       *   2行目以降
       *     事業所名: { 値: 空欄, 変更: 可 }
       *     総費用額: { 値: 空欄, 変更: 可 }
       *     利用者負担額: { 値: 空欄, 変更: 可 }
       *     管理結果後利用者負担額: { 値: 0, 変更: 不可 }
       * 利用者負担額の合計額が、負担上限月額以下のため、調整事務は行わない。(notCoordinated)
       *   1行目
       *     事業所名: { 値: 管理事業所（自事業所）を設定, 変更: 不可 }
       *     総費用額: { 値: 明細書の値から自動入力したい, 変更: 可 }
       *     利用者負担額: { 値: 明細書の値から自動入力したい, 変更: 可 }
       *     管理結果後利用者負担額: { 値: 利用者負担額の値と連動させる, 変更: 不可 }
       *   2行目以降
       *     事業所名: { 値: 空欄, 変更: 可 }
       *     総費用額: { 値: 空欄, 変更: 可 }
       *     利用者負担額: { 値: 空欄, 変更: 可 }
       *     管理結果後利用者負担額: { 値: 利用者負担額の値と連動させる, 変更: 不可 }
       * 利用者負担額の合計額が、負担上限月額を超過するため、下記のとおり調整した。(coordinated)
       *   1行目
       *     事業所名: { 値: 管理事業所（自事業所）を設定, 変更: 不可 }
       *     総費用額: { 値: 明細書の値から自動入力したい, 変更: 可 }
       *     利用者負担額: { 値: 明細書の値から自動入力したい, 変更: 可 }
       *     管理結果後利用者負担額: { 値: 明細書の利用者負担上限月額から自動入力したい, 変更: 可 }
       *   2行目以降
       *     事業所名: { 値: 空欄, 変更: 可 }
       *     総費用額: { 値: 空欄, 変更: 可 }
       *     利用者負担額: { 値: 空欄, 変更: 可 }
       *     利用者負担額: { 値: 空欄, 変更: 可 }
       */
      const createListItem = (
        item: PartialItem,
        override?: DeepPartial<Omit<ListItem, 'officeId'>>
      ) => ({
        deletable: override?.deletable ?? true,
        disabled: {
          coordinatedCopay: computed(() => {
            return override?.disabled?.coordinatedCopay ?? (isAppropriated.value || isNotCoordinated.value)
          }),
          officeId: override?.disabled?.officeId ?? false
        },
        officeId: computed<string | number | undefined>({
          get: () => item.officeId,
          set: v => { item.officeId = v ? toInt(v) : undefined }
        }),
        officeOptions: override?.officeOptions ?? officeOptions,
        subtotal: {
          fee: computed<LocalSubtotal['fee']>({
            get: () => item.subtotal.fee,
            set: v => { item.subtotal.fee = v ? toInt(v) : undefined }
          }),
          copay: override?.subtotal?.copay ?? computed<LocalSubtotal['copay']>({
            get: () => item.subtotal.copay,
            set: v => {
              const parsed = v ? toInt(v) : undefined
              item.subtotal.copay = parsed
              if (isNotCoordinated.value) {
                item.subtotal.coordinatedCopay = parsed
              }
            }
          }),
          coordinatedCopay: computed<LocalSubtotal['coordinatedCopay']>({
            get: () => item.subtotal.coordinatedCopay,
            set: v => { item.subtotal.coordinatedCopay = v ? toInt(v) : undefined }
          })
        }
      })
      const listItems = computed(() => {
        return items.value.map((x, i) => {
          const args = i === 0
            ? { deletable: false, disabled: { officeId: true } }
            : { officeOptions: filteredOfficeOptions }
          return createListItem(x, args)
        })
      })

      return {
        items: listItems
      }
    }
    const rules = computed<Rules>(() => {
      const isProvided = serviceProvisionStatus.isProvided.value
      const makeCustom = (targetName: string) => ({
        message: `${targetName}が利用者負担上限月額を超えないようにしてください。`,
        validate: (value: string) => parseInt(value) <= props.statement.user.copayLimit
      })
      const numberOfOffices = {
        message: '上限額管理を行う場合は利用者負担額集計・調整欄を2件以上登録してください。',
        validate: (value: number) => !isProvided || value >= 2
      }
      const officeOnly = {
        message: '他事業所におけるサービス提供がない場合は、利用者負担額集計・調整欄を1件のみ登録してください。',
        validate: (value: number) => isProvided || value === 1
      }
      const itemsRule = items.value.map((_, i) => ({
        subtotal: {
          fee: { required, numeric, minValue: 1 },
          copay: { required, numeric, maxValue: `@items.${i}.subtotal.fee` },
          coordinatedCopay: {
            required,
            numeric,
            maxValue: `@items.${i}.subtotal.copay`,
            custom: makeCustom('利用者負担額')
          }
        }
      }))
      return validationRules({
        isProvided: { required },
        exchangeAim: { required },
        result: { required },
        officeId: { required },
        // items.0.subtotal.hoge: {} のようなオブジェクトになるように展開する.
        items: { ...itemsRule },
        total: {
          coordinatedCopay: { custom: makeCustom('管理結果後利用者負担額の合計') }
        },
        numberOfOffices: { custom: numberOfOffices },
        officeOnly: { custom: officeOnly }
      })
    })
    const customErrorMessages = {
      officeId: {
        required: '事業所名を入力してください。'
      },
      fee: {
        required: '総費用額を入力してください。',
        minValue: '総費用額には1以上の値を入力してください。'
      },
      copay: {
        required: '利用者負担額を入力してください。',
        maxValue: '利用者負担額には総費用額以下の値を入力してください。'
      },
      coordinatedCopay: {
        required: '管理結果後利用者負担額を入力してください。',
        maxValue: '管理結果後利用者負担額には利用者負担額以下の値を入力してください。'
      }
    }
    const isNonEmptyArray = (array?: unknown[]) => Array.isArray(array) && array.length >= 1
    const hasObserverError = ({ errors }: ValidationObserverInstance, keys?: string[]) => {
      const objects = keys
        ? Object.fromEntries(Object.entries(errors).filter(([key, _]) => keys?.includes(key)))
        : errors
      return objects && Object.values(objects).some(v => v.length >= 1)
    }
    const getErrorMessages = ({ errors }: ValidationObserverInstance) => {
      if (!errors) {
        return []
      }
      const set = new Set()
      Object.values(errors).forEach(value => {
        value.forEach(v => set.add(v))
      })
      return Array.from(set)
    }
    const serviceProvisionStatus = useServiceProvisionStatus()
    const exchangeAim = useExchangeAim()
    return {
      ...useConditionalListItems(),
      ...useDwsBillingStatusIcon({ status: props.status }),
      addItem,
      result: currentResult,
      customErrorMessages,
      deleteItem,
      exchangeAim,
      getErrorMessages,
      hasObserverError,
      isLoadingOffices,
      isMobile: computed(() => $vuetify.breakpoint.mdAndDown),
      isNonEmptyArray,
      numeral,
      observer,
      resolveDwsBillingStatus,
      resultTypes,
      rules,
      serviceProvisionStatus,
      submit,
      totalPayment,
      user: props.statement.user
    }
  }
})
</script>

<style lang="scss" module>
.root {
  :global {
    .v-data-table.item-table > .v-data-table__wrapper > table {
      > thead th {
        padding: 0 12px;
      }

      > tbody td {
        padding: 0 4px;

        &:first-of-type {
          padding-left: 8px;
        }

        &:last-of-type {
          padding-right: 8px;
        }
      }

      tr {
        &:hover {
          background: inherit !important;
        }

        &.total > td {
          padding-top: 8px;
          padding-right: 15px;

          &:last-of-type {
            padding-right: 20px;
          }
        }

        &.no-border > td {
          border-bottom: none !important;
        }

        &.dummy-row {
          display: none;
        }
      }
    }

    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }
  }
}

.writingVertical {
  writing-mode: vertical-rl;
}

.noBullet {
  list-style: none;
}
</style>
