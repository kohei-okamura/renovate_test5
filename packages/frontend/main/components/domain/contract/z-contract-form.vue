<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-form data-form @submit.prevent="submit">
    <validation-observer ref="observer" tag="div">
      <z-user-summary :user="user" />
      <z-form-card title="契約情報">
        <z-form-card-item
          v-if="isProvisional"
          v-slot="{ errors }"
          data-contracted-on
          class="mt-3"
          vid="contractedOn"
        >
          <v-alert v-if="errors.length" class="ml-2 mr-4" dense type="error">
            {{ errors[0] }}
          </v-alert>
        </z-form-card-item>
        <z-data-card-item label="事業領域" :icon="$icons.category" :value="resolveServiceSegment(serviceSegment)" />
        <z-data-card-item label="契約状態" :icon="statusIcon">
          <span v-if="statusBefore !== statusAfter">{{ resolveContractStatus(statusBefore) }} ⇒</span>
          <span>{{ resolveContractStatus(statusAfter) }}</span>
        </z-data-card-item>
        <z-form-card-item-set v-if="isProvisional" :icon="$icons.office">
          <z-form-card-item v-slot="{ errors }" data-office-id vid="officeId" :rules="rules.officeId">
            <z-keyword-filter-autocomplete
              v-model="form.officeId"
              label="事業所 *"
              :error-messages="errors"
              :items="officeOptions"
              :loading="isLoadingOffices"
            />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-data-card-item v-else label="事業所" :icon="$icons.office" :value="resolveOfficeAbbr(form.officeId)" />
        <template v-if="isFormal || isTerminated">
          <z-form-card-item-set v-if="isFormal" :icon="$icons.dateStart">
            <z-form-card-item v-slot="{ errors }" data-contracted-on vid="contractedOn" :rules="rules.contractedOn">
              <z-date-field v-model="form.contractedOn" label="契約日 *" :error-messages="errors" />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-data-card-item v-else label="契約日" :icon="$icons.dateStart">
            <z-era-date :value="form.contractedOn" />
          </z-data-card-item>
          <template v-if="isTerminated">
            <z-form-card-item-set :icon="$icons.dateEnd">
              <z-form-card-item v-slot="{ errors }" data-terminated-on vid="terminatedOn" :rules="rules.terminatedOn">
                <z-date-field v-model="form.terminatedOn" label="解約日 *" :error-messages="errors" />
              </z-form-card-item>
              <z-form-card-item
                v-if="isLtcs"
                v-slot="{ errors }"
                data-expired-reason
                vid="expiredReason"
                :rules="rules.expiredReason"
              >
                <z-select
                  v-model="form.expiredReason"
                  label="解約理由 *"
                  :error-messages="errors"
                  :items="expiredReasonOptions"
                />
              </z-form-card-item>
            </z-form-card-item-set>
          </template>
        </template>
        <z-form-card-item-set :icon="$icons.text">
          <z-form-card-item v-slot="{ errors }" data-note vid="note" :rules="rules.note">
            <z-textarea v-model.trim="form.note" label="備考" :counter="rules.note.max" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <template v-if="isDws && (isFormal || isTerminated)">
        <z-form-card v-for="x in divisions" :key="x.code" :data-division="x.code" :title="x.title">
          <z-form-card-item-set :icon="$icons.dateStart">
            <z-form-card-item
              v-slot="{ errors }"
              :data-dws-periods-start="x.code"
              :rules="rules.dwsPeriods[x.code].start"
              :vid="`dwsPeriods.${x.code}.start`"
            >
              <z-date-field v-model="form.dwsPeriods[x.code].start" label="初回サービス提供日" :error-messages="errors" />
            </z-form-card-item>
          </z-form-card-item-set>
          <z-form-card-item-set :icon="$icons.dateEnd">
            <z-form-card-item
              v-slot="{ errors }"
              :data-dws-periods-end="x.code"
              :rules="rules.dwsPeriods[x.code].end"
              :vid="`dwsPeriods.${x.code}.end`"
            >
              <z-date-field v-model="form.dwsPeriods[x.code].end" label="最終サービス提供日" :error-messages="errors" />
            </z-form-card-item>
          </z-form-card-item-set>
        </z-form-card>
      </template>
      <z-form-card v-else-if="isLtcs && (isFormal || isTerminated)" title="介護保険サービス">
        <z-form-card-item-set :icon="$icons.dateStart">
          <z-form-card-item
            v-slot="{ errors }"
            data-ltcs-period-start
            :rules="rules.ltcsPeriod.start"
            :vid="`ltcsPeriod.start`"
          >
            <z-date-field v-model="form.ltcsPeriod.start" label="初回サービス提供日" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
        <z-form-card-item-set :icon="$icons.dateEnd">
          <z-form-card-item
            v-slot="{ errors }"
            data-ltcs-period-end
            :rules="rules.ltcsPeriod.end"
            :vid="`ltcsPeriod.end`"
          >
            <z-date-field v-model="form.ltcsPeriod.end" label="最終サービス提供日" :error-messages="errors" />
          </z-form-card-item>
        </z-form-card-item-set>
      </z-form-card>
      <z-form-action-button :disabled="progress" :icon="$icons.save" :loading="progress" :text="buttonText" />
    </validation-observer>
  </v-form>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs } from '@nuxtjs/composition-api'
import { ContractStatus, resolveContractStatus } from '@zinger/enums/lib/contract-status'
import { DwsServiceDivisionCode } from '@zinger/enums/lib/dws-service-division-code'
import { LtcsExpiredReason } from '@zinger/enums/lib/ltcs-expired-reason'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveServiceSegment, ServiceSegment } from '@zinger/enums/lib/service-segment'
import { isEmpty, nonEmpty } from '@zinger/helpers'
import { enumerableOptions } from '~/composables/enumerable-options'
import { useContractStatusIcon } from '~/composables/use-contract-status-icon'
import { FormProps, getFormPropsOptions, useFormBindings } from '~/composables/use-form-bindings'
import { useOffices } from '~/composables/use-offices'
import { User } from '~/models/user'
import { DwsContractsApi } from '~/services/api/dws-contracts-api'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'
import { $datetime } from '~/services/datetime-service'
import { numeric, required } from '~/support/validation/rules'
import { Rules } from '~/support/validation/types'

type Form =
  DwsContractsApi.CreateForm |
  DwsContractsApi.UpdateForm |
  LtcsContractsApi.CreateForm |
  LtcsContractsApi.UpdateForm

type Props = FormProps<Form> & Readonly<{
  buttonText: string
  contractStatus: ContractStatus
  permission: Permission
  serviceSegment: ServiceSegment
  user: User
}>

export default defineComponent<Props>({
  name: 'ZContractForm',
  props: {
    ...getFormPropsOptions(),
    buttonText: { type: String, required: true },
    contractStatus: { type: Number, default: ContractStatus.provisional },
    permission: { type: String, required: true },
    serviceSegment: { type: Number, required: true },
    user: { type: Object, required: true }
  },
  setup: (props: Props, context) => {
    const propRefs = toRefs(props)
    const { form, observer, submit } = useFormBindings<Form>(props, context, {
      init: input => {
        // この時点ではまだ各種 computed なフラグを参照できないので直接判定する
        const isLtcs = props.serviceSegment === ServiceSegment.longTermCare
        const isTerminated = input.status === ContractStatus.terminated
        const isUnspecified = input.expiredReason === LtcsExpiredReason.unspecified
        return {
          ...input,
          expiredReason: isLtcs && isTerminated && isUnspecified ? undefined : input.expiredReason
        } as Form
      }
    })
    const statusBefore = computed(() => props.contractStatus)
    const statusAfter = computed(() => form.status ?? ContractStatus.provisional)

    const divisions = DwsServiceDivisionCode.values.map(code => ({
      code,
      title: DwsServiceDivisionCode.resolve(code)
    }))
    const expiredReasonOptions = enumerableOptions(LtcsExpiredReason).filter(x => {
      return x.value !== LtcsExpiredReason.unspecified
    })

    const isDws = computed(() => props.serviceSegment === ServiceSegment.disabilitiesWelfare)
    const isFormal = computed(() => statusAfter.value === ContractStatus.formal)
    const isLtcs = computed(() => props.serviceSegment === ServiceSegment.longTermCare)
    const isProvisional = computed(() => statusAfter.value === ContractStatus.provisional)
    const isTerminated = computed(() => statusAfter.value === ContractStatus.terminated)

    const rules = computed<Rules>(() => ({
      officeId: { required, numeric },
      contractedOn: { required },
      terminatedOn: {
        required,
        minDate: [$datetime.parse(form.contractedOn)?.plus({ days: 1 }), '契約日']
      },
      dwsPeriods: isDws.value && !isProvisional.value
        ? Object.fromEntries(DwsServiceDivisionCode.values.map(code => [code, {
          start: {
            required: DwsServiceDivisionCode.values.every(x => {
              return isEmpty(form.dwsPeriods?.[x].start)
            }),
            minDate: [$datetime.parse(form.contractedOn), '契約日']
          },
          end: {
            required: isTerminated.value && nonEmpty(form.dwsPeriods?.[code].start),
            minDate: [$datetime.parse(form.dwsPeriods?.[code].start)?.plus({ days: 1 }), '初回サービス提供日']
          }
        }]))
        : undefined,
      ltcsPeriod: isLtcs.value && !isProvisional.value
        ? {
          start: { required, minDate: [$datetime.parse(form.contractedOn), '契約日'] },
          end: {
            required: isTerminated.value,
            minDate: [$datetime.parse(form.ltcsPeriod?.start)?.plus({ days: 1 }), '初回サービス提供日']
          }
        }
        : undefined,
      expiredReason: { required: isLtcs.value && isTerminated.value },
      note: { max: 255 }
    }))

    return {
      ...useContractStatusIcon(propRefs.contractStatus),
      ...useOffices({ permission: propRefs.permission, internal: true }),
      divisions,
      expiredReasonOptions,
      form,
      isDws,
      isFormal,
      isLtcs,
      isProvisional,
      isTerminated,
      observer,
      resolveServiceSegment,
      resolveContractStatus,
      rules,
      statusAfter,
      statusBefore,
      submit
    }
  }
})
</script>
