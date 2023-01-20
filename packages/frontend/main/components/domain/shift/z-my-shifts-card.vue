<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div data-z-my-shifts-card>
    <z-subheader>スケジュール</z-subheader>
    <v-card>
      <transition mode="out-in" name="fade">
        <div v-if="isResolved" key="resolved" class="pb-4">
          <div v-for="(date, i) in dates" :key="date" :class="$style.date">
            <v-subheader class="subtitle-1">
              <span v-if="i === 0" class="font-weight-bold">今日&nbsp;—&nbsp;</span>
              <span v-else-if="i === 1" class="font-weight-bold">明日&nbsp;—&nbsp;</span>
              <z-date :value="date" />
              <span>（{{ localeDate(date, { weekday: 'narrow' }) }}）</span>
            </v-subheader>
            <v-card-text v-if="groupedShifts.has(date)" class="pt-0">
              <z-shift-list :value="groupedShifts.get(date)" />
            </v-card-text>
            <v-card-text v-else class="pt-0">
              <span class="z-my-shifts-card_no-data">勤務シフトはありません。</span>
            </v-card-text>
          </div>
        </div>
        <v-card-text v-else key="not-resolved" class="text-center">
          <v-progress-circular color="secondary" indeterminate size="64" />
        </v-card-text>
      </transition>
    </v-card>
  </div>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { assert, range } from '@zinger/helpers'
import { Seq } from 'immutable'
import { localeDate } from '~/composables/locale-date'
import { sessionStateKey } from '~/composables/stores/use-session-store'
import { useShiftsStore } from '~/composables/stores/use-shifts-store'
import { useAsync } from '~/composables/use-async'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'

export default defineComponent({
  name: 'ZMyShiftsCard',
  setup () {
    const { $datetime } = usePlugins()
    const now = $datetime.now
    const shiftsStore = useShiftsStore()
    const sessionState = useInjected(sessionStateKey)
    const { isResolved } = useAsync(() => {
      const auth = sessionState.auth.value
      assert(auth !== undefined, 'NOT AUTHENTICATED')
      return shiftsStore.getIndex({
        assigneeId: auth.staff.id,
        sortBy: 'date',
        start: now.toISODate(),
        end: now.plus({ days: 14 }).toISODate(),
        all: true
      })
    })
    return {
      dates: range(0, 13).map(days => now.plus({ days }).toISODate()),
      groupedShifts: computed(() => {
        return Seq(shiftsStore.state.shifts.value)
          .sortBy(x => $datetime.parse(x.schedule.start).toMillis())
          .groupBy(x => $datetime.parse(x.schedule.date).toISODate())
      }),
      isResolved,
      localeDate
    }
  }
})
</script>

<style lang="scss" module>
.date {
  padding-top: 16px;
}
</style>
