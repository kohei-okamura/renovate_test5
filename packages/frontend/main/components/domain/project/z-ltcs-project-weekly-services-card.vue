<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-data-card data-z-project-weekly-services-card :title="`週間サービス計画表（No.${program.programIndex}）`">
    <z-data-card-item
      label="サービス区分"
      :icon="$icons.category"
      :value="resolveLtcsProjectServiceCategory(program.category)"
    />
    <z-data-card-item label="繰り返し周期" :icon="$icons.recurrence" :value="resolveRecurrence(program.recurrence)" />
    <div :class="{ 'd-flex': $vuetify.breakpoint.smAndUp }">
      <z-data-card-item label="曜日" :icon="$icons.schedule">
        <template v-for="(dayOfWeek, i) in dayOfWeeks">
          <span :key="i" class="mr-1" :class="{'text--disabled': !program.dayOfWeeks.includes(dayOfWeek)}">
            {{ resolveDayOfWeek(dayOfWeek) }}
          </span>
        </template>
      </z-data-card-item>
      <z-data-card-item label="時間帯" :icon="$icons.blank">
        {{ program.slot.start }} ~ {{ program.slot.end }}
      </z-data-card-item>
      <z-data-card-item label="算定時間帯" :icon="$icons.blank">
        {{ resolveTimeframe(program.timeframe) }}
      </z-data-card-item>
      <z-data-card-item label="時間" :icon="$icons.blank" :value="serviceElapsedMinute + '分'" />
    </div>
    <z-data-card-item label="サービス提供量" :icon="$icons.amount">
      <div v-for="(x, i) in program.amounts" :key="i">
        <v-chip label small>
          {{ resolveLtcsProjectAmountCategory(x.category) }}
        </v-chip>
        （{{ x.amount }}分）
      </div>
    </z-data-card-item>
    <z-data-card-item label="提供人数" :icon="$icons.headcount" :value="program.headcount" />
    <z-data-card-item
      v-if="program.ownExpenseProgramId"
      label="自費サービス情報"
      :icon="$icons.ownExpenseProgram"
      :value="resolveOwnExpenseProgramName(program.ownExpenseProgramId)"
    />
    <z-data-card-item
      v-if="!program.ownExpenseProgramId"
      label="サービスコード"
      :icon="$icons.serviceCode"
      :value="program.serviceCode"
    />
    <z-data-card-item label="サービスオプション" :icon="$icons.serviceOption">
      <div v-if="program.options.length === 0">-</div>
      <template v-else>
        <v-chip v-for="x in program.options" :key="x" label small>{{ resolveServiceOption(x) }}</v-chip>
      </template>
    </z-data-card-item>
    <z-data-card-item
      label="備考"
      :icon="$icons.note"
      :value="program.note || '-'"
    />
    <z-overflow-shadow>
      <v-data-table
        class="text-no-wrap"
        :class="$style.servicesTable"
        fixed-header
        hide-default-footer
        mobile-breakpoint="0"
        :dense="$vuetify.breakpoint.smAndDown"
        :headers="headers"
        :items="serviceContents"
      >
        <template #item.menuId="{ item }">{{ resolveLtcsProjectServiceMenuName(item.menuId) }}</template>
        <template #item.duration="{ item }">{{ !!item.duration ? item.duration + '分' : '' }}</template>
      </v-data-table>
    </z-overflow-shadow>
    <div v-if="isExpandable" class="text-center">
      <v-btn color="primary" min-width="150" text @click="toggleExpanded">
        <v-icon left>{{ isExpanded ? $icons.shrink : $icons.expand }}</v-icon>
        <span>{{ isExpanded ? '閉じる' : 'もっとみる' }}</span>
      </v-btn>
    </div>
  </z-data-card>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, toRefs } from '@nuxtjs/composition-api'
import { DayOfWeek, resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import { resolveLtcsProjectAmountCategory } from '@zinger/enums/lib/ltcs-project-amount-category'
import { resolveLtcsProjectServiceCategory } from '@zinger/enums/lib/ltcs-project-service-category'
import { resolveRecurrence } from '@zinger/enums/lib/recurrence'
import { resolveServiceOption } from '@zinger/enums/lib/service-option'
import { resolveTimeframe } from '@zinger/enums/lib/timeframe'
import { useOwnExpenseProgramResolverStore } from '~/composables/stores/use-own-expense-program-resolver-store'
import { useLtcsProjectServiceMenuResolver } from '~/composables/use-ltcs-project-service-menu-resolver'
import { LtcsProjectProgram } from '~/models/ltcs-project-program'
import { TimeDuration } from '~/models/time-duration'

type Props = {
  program: LtcsProjectProgram
}

const DEFAULT_NUM_ROWS = 4

export default defineComponent<Props>({
  name: 'ZLtcsProjectWeeklyServicesCard',
  props: {
    program: { type: Object, required: true }
  },
  setup: props => {
    const { state: { resolveOwnExpenseProgramName } } = useOwnExpenseProgramResolverStore()
    const { resolveLtcsProjectServiceMenuName } = useLtcsProjectServiceMenuResolver()
    const data = reactive({
      isExpanded: false
    })
    const serviceElapsedMinute = computed(() => {
      return TimeDuration.diff(props.program.slot.start, props.program.slot.end).get.totalMinutes
    })
    const isExpandable = computed(() => props.program.contents.length > DEFAULT_NUM_ROWS)
    const serviceContents = computed(() => {
      return data.isExpanded || props.program.contents.length <= DEFAULT_NUM_ROWS
        ? props.program.contents
        : props.program.contents.slice(0, DEFAULT_NUM_ROWS)
    })
    const toggleExpanded = () => {
      data.isExpanded = !data.isExpanded
    }
    return {
      ...toRefs(data),
      headers: [
        { text: 'サービス内容', value: 'menuId', sortable: false, width: 215 },
        { text: 'サービスの具体的内容', value: 'content', sortable: false },
        { text: '所要時間', align: 'right', value: 'duration', sortable: false, width: 80 },
        { text: '留意事項', value: 'memo', class: 'th-memo', sortable: false }
      ],
      serviceElapsedMinute,
      dayOfWeeks: DayOfWeek.values,
      isExpandable,
      resolveDayOfWeek,
      resolveLtcsProjectAmountCategory,
      resolveLtcsProjectServiceCategory,
      resolveLtcsProjectServiceMenuName,
      resolveOwnExpenseProgramName,
      resolveRecurrence,
      resolveServiceOption,
      resolveTimeframe,
      serviceContents,
      toggleExpanded
    }
  }
})
</script>

<style lang="scss" module>
.servicesTable {
  tr td:first-child {
    max-width: 215px;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
  }

  tr:hover:not(.v-table__expanded__content) {
    background: none !important;
  }
}
</style>
