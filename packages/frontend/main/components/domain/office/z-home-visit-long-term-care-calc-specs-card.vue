<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-accordion
    class="z-home-visit-long-term-care-calc-specs-card"
    :items="accordionItems"
    :options="accordionOptions"
  >
    <template #item.period="{ item }">
      <z-era-date :value="item.period.start" />
      <span>〜</span>
      <z-era-date :value="item.period.end" />
    </template>
    <template #item.locationAddition="{ item }">
      {{ resolveLtcsOfficeLocationAddition(item.locationAddition) }}
    </template>
    <template #item.specifiedOfficeAddition="{ item }">
      {{ resolveHomeHelpServiceSpecifiedOfficeAddition(item.specifiedOfficeAddition) }}
    </template>
    <template #item.specifiedTreatmentImprovementAddition="{ item }">
      {{ resolveDwsSpecifiedTreatmentImprovementAddition(item.specifiedTreatmentImprovementAddition) }}
    </template>
    <template #item.treatmentImprovementAddition="{ item }">
      {{ resolveDwsTreatmentImprovementAddition(item.treatmentImprovementAddition) }}
    </template>
    <template #item.baseIncreaseSupportAddition="{ item }">
      {{ resolveLtcsBaseIncreaseSupportAddition(item.baseIncreaseSupportAddition) }}
    </template>
    <template #footer>
      <v-btn v-if="isExpandable" block color="white" data-toggle-expanded-btn @click="toggleExpanded">
        {{ isExpanded ? '古い算定情報（介保・訪問介護）を隠す' : '古い算定情報（介保・訪問介護）を表示' }}
        <v-icon right>{{ isExpanded ? $icons.shrink : $icons.expand }}</v-icon>
      </v-btn>
    </template>
  </z-data-accordion>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, toRefs } from '@nuxtjs/composition-api'
import { resolveDwsSpecifiedTreatmentImprovementAddition } from '@zinger/enums/lib/dws-specified-treatment-improvement-addition'
import { resolveDwsTreatmentImprovementAddition } from '@zinger/enums/lib/dws-treatment-improvement-addition'
import { resolveHomeHelpServiceSpecifiedOfficeAddition } from '@zinger/enums/lib/home-help-service-specified-office-addition'
import { resolveLtcsBaseIncreaseSupportAddition } from '@zinger/enums/lib/ltcs-base-increase-support-addition'
import { resolveLtcsOfficeLocationAddition } from '@zinger/enums/lib/ltcs-office-location-addition'
import { useAuth } from '~/composables/use-auth'
import { HomeVisitLongTermCareCalcSpec } from '~/models/home-visit-long-term-care-calc-spec'
import { Office } from '~/models/office'
import { ZDataAccordionOptions } from '~/models/z-data-accordion-options'

type Props = {
  items: HomeVisitLongTermCareCalcSpec[]
  office: Office
}

const DEFAULT_NUM_ROWS = 2

export default defineComponent<Props>({
  name: 'ZHomeVisitLongTermCareCalcSpecsCard',
  props: {
    items: { type: Array, required: true },
    office: { type: Object, required: true }
  },
  setup: props => {
    const { permissions } = useAuth()
    const data = reactive({
      isExpanded: false
    })
    const accordionItems = computed(() => {
      return data.isExpanded || props.items.length <= DEFAULT_NUM_ROWS
        ? props.items
        : props.items.slice(0, DEFAULT_NUM_ROWS)
    })
    const accordionOptions = computed<ZDataAccordionOptions<HomeVisitLongTermCareCalcSpec>>(() => ({
      content: '算定情報（介保・訪問介護）',
      headers: [
        { text: '適用期間', value: 'period' },
        { text: '地域加算', value: 'locationAddition' },
        { text: '特定事業所加算', value: 'specifiedOfficeAddition' },
        { text: '処遇改善加算', value: 'treatmentImprovementAddition' },
        { text: '特定処遇改善加算', value: 'specifiedTreatmentImprovementAddition' },
        { text: 'ベースアップ等支援加算', value: 'baseIncreaseSupportAddition' }
      ],
      itemLink: x => `/offices/${props.office.id}/home-visit-long-term-care-calc-specs/${x.id}/edit`,
      itemLinkPermissions: [permissions.updateInternalOffices, permissions.updateExternalOffices],
      itemLinkText: '編集',
      title: '算定情報（介保・訪問介護）'
    }))
    const isExpandable = computed(() => props.items.length > DEFAULT_NUM_ROWS)
    const toggleExpanded = () => {
      data.isExpanded = !data.isExpanded
    }
    return {
      ...toRefs(data),
      accordionItems,
      accordionOptions,
      isExpandable,
      resolveLtcsBaseIncreaseSupportAddition,
      resolveLtcsOfficeLocationAddition,
      resolveHomeHelpServiceSpecifiedOfficeAddition,
      resolveDwsSpecifiedTreatmentImprovementAddition,
      resolveDwsTreatmentImprovementAddition,
      toggleExpanded
    }
  }
})
</script>
