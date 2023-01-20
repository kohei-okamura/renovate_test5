<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-card title="介護給付費明細書の状態">
    <v-simple-table dense>
      <thead>
        <tr>
          <th v-for="(item, j) in headers" :key="`status-${j}`" :class="`text-${item.align}`">
            {{ item.text }}
          </th>
        </tr>
      </thead>
      <tbody v-if="hasStatements">
        <z-with
          v-for="providedIn in providedInList"
          :key="`status-${providedIn}`"
          v-slot="{ data }"
          tag="tr"
          :value="aggregate[providedIn]"
        >
          <td class="text-start">{{ eraMonth(providedIn) }}</td>
          <td class="text-end">{{ data.checking }}</td>
          <td class="text-end">{{ data.ready }}</td>
          <td class="text-end">{{ data.fixed }}</td>
          <td class="text-end">{{ data.total }}</td>
        </z-with>
      </tbody>
      <tbody v-else>
        <tr>
          <th class="pt-2 text-center" :colspan="headers.length">データがありません</th>
        </tr>
      </tbody>
    </v-simple-table>
  </z-data-card>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs } from '@nuxtjs/composition-api'
import { LtcsBillingStatus, resolveLtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { keys } from '@zinger/helpers'
import { eraMonth } from '~/composables/era-date'
import { LtcsBillingStoreStatusAggregate } from '~/composables/stores/use-ltcs-billing-store'

type Props = {
  aggregate: LtcsBillingStoreStatusAggregate
  hasStatements: boolean
}

export default defineComponent<Props>({
  name: 'ZLtcsBillingStatusAggregateCard',
  props: {
    aggregate: { type: Object, required: true },
    hasStatements: { type: Boolean, required: true }
  },
  setup (props) {
    const reactiveProps = toRefs(props)
    return {
      eraMonth,
      headers: [
        { text: 'サービス提供年月', align: 'start' },
        { text: resolveLtcsBillingStatus(LtcsBillingStatus.checking), align: 'end' },
        { text: resolveLtcsBillingStatus(LtcsBillingStatus.ready), align: 'end' },
        { text: resolveLtcsBillingStatus(LtcsBillingStatus.fixed), align: 'end' },
        { text: '合計', align: 'end' }
      ],
      providedInList: computed(() => keys(reactiveProps.aggregate.value).sort().reverse())
    }
  }
})
</script>
