<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog max-width="480" transition="dialog" :value="show" :width="width" @input="v => v || close()">
    <v-card v-if="value">
      <z-card-titlebar color="blue-grey">サービス情報</z-card-titlebar>
      <z-data-card-item
        label="サービス区分"
        :icon="$icons.category"
        :value="resolveLtcsProjectServiceCategory(value.category)"
      />
      <z-data-card-item
        v-if="isOwnExpense"
        label="自費サービス"
        :value="resolveOwnExpenseProgramName(value.ownExpenseProgramId)"
      />
      <z-data-card-item label="サービス提供時間" :icon="$icons.schedule">
        <z-datetime :value="value.slot.start" />
        <span>〜</span>
        <z-time :value="value.slot.end" />
      </z-data-card-item>
      <z-data-card-item label="算定時間帯" :icon="$icons.timeframe" :value="resolveTimeframe(value.timeframe)" />
      <z-data-card-item
        v-for="v in value.amounts"
        :key="v.category"
        :label="resolveLtcsProjectAmountCategory(v.category)"
        :value="`${v.amount} 分`"
      />
      <z-data-card-item label="提供人数" :icon="$icons.headcount" :value="value.headcount" />
      <z-data-card-item
        v-if="!isOwnExpense"
        label="サービスコード"
        :icon="$icons.serviceCode"
      >
        <z-promised
          v-slot="{ data }"
          tag="span"
          :data-service-code="value.serviceCode"
          :promise="lookupLtcsHomeVisitLongTermCareDictionary(value.serviceCode)"
        >
          {{ value.serviceCode }}: {{ data }}
        </z-promised>
      </z-data-card-item>
      <z-data-card-item label="サービスオプション" :icon="$icons.serviceOption">
        <div v-if="value.options.length === 0">-</div>
        <template v-else>
          <v-chip v-for="x in value.options" :key="x" label small>{{ resolveServiceOption(x) }}</v-chip>
        </template>
      </z-data-card-item>
      <z-data-card-item label="備考" :icon="$icons.note" :value="value.note || '-'" />
      <v-row class="px-4 py-3" justify="center" justify-md="end" no-gutters>
        <v-col cols="5" md="3">
          <v-btn data-close text width="100%" @click.stop="close">閉じる</v-btn>
        </v-col>
      </v-row>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { resolveLtcsProjectAmountCategory } from '@zinger/enums/lib/ltcs-project-amount-category'
import { LtcsProjectServiceCategory, resolveLtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { resolveServiceOption } from '@zinger/enums/lib/service-option'
import { resolveTimeframe } from '@zinger/enums/lib/timeframe'
import { date } from '~/composables/date'
import { useOwnExpenseProgramResolverStore } from '~/composables/stores/use-own-expense-program-resolver-store'
import { useLtcsHomeVisitLongTermCareDictionary } from '~/composables/use-ltcs-home-visit-long-term-care-dictionary'
import { DateLike } from '~/models/date'
import { LtcsProvisionReportEntry } from '~/models/ltcs-provision-report-entry'
import { OfficeId } from '~/models/office'

type Props = {
  isEffectiveOn: DateLike
  officeId: OfficeId
  show: boolean
  value?: LtcsProvisionReportEntry
  width: string
}
export default defineComponent<Props>({
  name: 'ZLtcsProvisionReportEntryBrowsingDialog',
  props: {
    isEffectiveOn: { type: [String, Object], required: true },
    officeId: { type: Number, required: true },
    show: { type: Boolean, required: true },
    value: { type: Object, default: undefined },
    width: { type: String, required: true }
  },
  setup (props: Props, context) {
    const close = () => {
      context.emit('click:close')
    }
    const isOwnExpense = computed(() => props.value?.category === LtcsProjectServiceCategory.ownExpense)
    const { lookupLtcsHomeVisitLongTermCareDictionary } = useLtcsHomeVisitLongTermCareDictionary(
      props.officeId,
      props.isEffectiveOn
    )
    return {
      close,
      date,
      isOwnExpense,
      lookupLtcsHomeVisitLongTermCareDictionary,
      resolveLtcsProjectAmountCategory,
      resolveLtcsProjectServiceCategory,
      resolveOwnExpenseProgramName: useOwnExpenseProgramResolverStore().state.resolveOwnExpenseProgramName,
      resolveServiceOption,
      resolveTimeframe
    }
  }
})
</script>
