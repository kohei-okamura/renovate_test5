<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-card title="サービス明細" :class="$style.root">
    <z-overflow-shadow>
      <v-simple-table :dense="$vuetify.breakpoint.smAndDown" class="text-no-wrap service-details">
        <thead>
          <tr>
            <th>摘要</th>
            <th class="text-right">数量</th>
            <th class="text-right">単価</th>
            <th class="text-right">小計</th>
            <th class="text-right">金額</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, i) in rows" :key="i">
            <td>{{ item.abstract }}</td>
            <td class="text-right">{{ numeralWithUnit(item.quantity, '単位') }}</td>
            <td class="text-right">{{ isEmpty(item.unitCost) ? '' : `${numeralWithDivision(item.unitCost)}円` }}</td>
            <td class="text-right">{{ numeralWithUnit(item.subtotal) }}</td>
            <td class="text-right">{{ numeralWithUnit(item.amount) }}</td>
          </tr>
        </tbody>
      </v-simple-table>
    </z-overflow-shadow>
  </z-data-card>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { UserBillingResult } from '@zinger/enums/lib/user-billing-result'
import { isEmpty } from '@zinger/helpers'
import { numeral, numeralWithDivision } from '~/composables/numeral'
import { useAuth } from '~/composables/use-auth'
import { UserBilling } from '~/models/user-billing'

type Props = Readonly<{
  userBilling: UserBilling
}>

type BillingRow = {
  quantity: number
  subtotal: number
  unitCost: number
  abstract: string
} | {
  subtotal: number
  abstract: string
} | {
  amount: number
  abstract: string
}

export default defineComponent<Props>({
  name: 'ZUserBillingItemCard',
  props: {
    userBilling: { type: Object, required: true }
  },
  setup (props) {
    const createBillingRows = (service: string, key: Extract<keyof UserBilling, 'dwsItem' | 'ltcsItem'>): BillingRow[] => {
      const item = props.userBilling && props.userBilling[key]
      if (!item) {
        return []
      }
      return [
        { abstract: service, quantity: item.score, unitCost: item.unitCost, subtotal: item.subtotalCost },
        { abstract: `${service} 介護給付額`, subtotal: item.benefitAmount },
        ...(item.subsidyAmount > 0
          ? [{ abstract: `${service} ${key === 'dwsItem' ? '自治体助成額' : '公費負担額'}`, subtotal: item.subsidyAmount }]
          : []),
        { abstract: `${service} 自己負担額`, amount: item.copayWithTax }
      ]
    }
    const othersAmount = props.userBilling?.otherItems.map(x => x.copayWithTax).reduce((x, y) => x + y, 0) ?? 0
    const dwsRows = computed(() => createBillingRows('障害福祉サービス', 'dwsItem'))
    const ltcsRows = computed(() => createBillingRows('介護保険サービス', 'ltcsItem'))
    const otherRows = computed(() => {
      return props.userBilling?.otherItems ? [{ abstract: '自己負担サービス', amount: othersAmount }] : []
    })
    return {
      ...useAuth(),
      isEmpty,
      isUserBillingResultPending: computed(() => props.userBilling?.result === UserBillingResult.pending),
      numeralWithDivision,
      numeralWithUnit: (x?: number, unit = '円') => isEmpty(x) ? '' : `${x < 0 ? '▲' : ''}${numeral(Math.abs(x))}${unit}`,
      rows: computed(() => [...dwsRows.value, ...ltcsRows.value, ...otherRows.value])
    }
  }
})
</script>

<style lang="scss" module>
.root {
  :global {
    .v-data-table.service-details > .v-data-table__wrapper > table {
      tr:hover {
          background: inherit !important;
      }
    }
  }
}
</style>
