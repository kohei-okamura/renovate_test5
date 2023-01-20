<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-table :items="items" :options="tableOptions">
    <template #item.content="{ item }">
      <z-promised
        v-slot="{ data }"
        tag="span"
        :data-service-code="item.serviceCode"
        :promise="lookupLtcsHomeVisitLongTermCareName(item.serviceCode)"
      >
        <span>{{ data }}</span>
      </z-promised>
    </template>
    <template #item.unitScore="{ item }">{{ numeral(item.unitScore) }}</template>
    <template #item.count="{ item }">{{ numeral(item.count) }}</template>
    <template #item.totalScore="{ item }">{{ numeral(item.totalScore) }}</template>
  </z-data-table>
</template>

<script lang="ts">
import { defineComponent, toRefs } from '@nuxtjs/composition-api'
import { appendHeadersCommonProperty, dataTableOptions } from '~/composables/data-table-options'
import { numeral } from '~/composables/numeral'
import { useLookupLtcsHomeVisitLongTermCareName } from '~/composables/use-ltcs-home-visit-long-term-care-name'
import { DateLike } from '~/models/date'
import { LtcsBillingStatement } from '~/models/ltcs-billing-statement'
import { LtcsBillingStatementItem } from '~/models/ltcs-billing-statement-item'
import { OfficeId } from '~/models/office'

type Props = {
  items: LtcsBillingStatementItem[]
  officeId: OfficeId
  providedIn: DateLike
}

export default defineComponent<Props>({
  name: 'ZLtcsBillingStatementItemListCard',
  props: {
    items: { type: Array, required: true },
    officeId: { type: Number, required: true },
    providedIn: { type: [String, Object], required: true }
  },
  setup (props: Props) {
    const propRefs = toRefs(props)
    const tableOptions = dataTableOptions<LtcsBillingStatement>({
      content: '明細',
      headers: appendHeadersCommonProperty([
        { text: 'サービス内容', value: 'content' },
        { text: 'サービスコード', value: 'serviceCode', width: 120 },
        { text: '単位数', value: 'unitScore', align: 'end', width: 70 },
        { text: '回数', value: 'count', align: 'end', width: 60 },
        { text: 'サービス単位数', value: 'totalScore', align: 'end', width: 120 },
        { text: '摘要', value: 'note', width: 60 }
      ]),
      title: '給付費明細欄'
    })
    return {
      ...useLookupLtcsHomeVisitLongTermCareName(propRefs.providedIn),
      numeral: (x: number | '-' = '-', format: string = '0,0') => numeral(x, format),
      tableOptions
    }
  }
})
</script>
