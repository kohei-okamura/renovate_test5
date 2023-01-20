<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <v-dialog max-width="480" transition="dialog" :value="show" :width="width" @input="v => v || close()">
    <v-card v-if="value">
      <z-card-titlebar color="blue-grey">{{ target }}</z-card-titlebar>
      <z-data-card-item
        label="サービス区分"
        :icon="$icons.category"
        :value="resolveDwsProjectServiceCategory(value.category)"
      />
      <z-data-card-item
        v-if="isOwnExpense"
        label="自費サービス"
        :value="resolveOwnExpenseProgramName(value.ownExpenseProgramId)"
      />
      <z-data-card-item
        v-if="isVisitingCareForPwsd"
        label="移動介護時間数"
        :icon="$icons.timeAmount"
        :value="`${value.movingDurationMinutes} 分`"
      />
      <z-data-card-item label="サービス提供年月日" :icon="$icons.schedule" :value="date(value.schedule.date)" />
      <z-data-card-item label="サービス提供時間" :icon="$icons.schedule">
        <z-datetime :value="value.schedule.start" />
        <span>〜</span>
        <z-time :value="value.schedule.end" />
      </z-data-card-item>
      <z-data-card-item label="提供人数" :icon="$icons.headcount" :value="value.headcount" />
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
import {
  DwsProjectServiceCategory,
  resolveDwsProjectServiceCategory
} from '@zinger/enums/lib/dws-project-service-category'
import { resolveServiceOption } from '@zinger/enums/lib/service-option'
import { date } from '~/composables/date'
import { useOwnExpenseProgramResolverStore } from '~/composables/stores/use-own-expense-program-resolver-store'
import { DwsProvisionReportItem } from '~/models/dws-provision-report-item'

type Props = {
  show: boolean
  target: string
  value?: DwsProvisionReportItem
  width: string
}

export default defineComponent<Props>({
  name: 'ZDwsProvisionReportItemBrowsingDialog',
  props: {
    show: { type: Boolean, required: true },
    value: { type: Object, default: undefined },
    target: { type: String, required: true },
    width: { type: String, required: true }
  },
  setup (props: Props, context) {
    const isOwnExpense = computed(() => props.value?.category === DwsProjectServiceCategory.ownExpense)
    const isVisitingCareForPwsd = computed(() => {
      return props.value?.category === DwsProjectServiceCategory.visitingCareForPwsd
    })
    const close = () => {
      context.emit('click:close')
    }
    return {
      close,
      date,
      isOwnExpense,
      isVisitingCareForPwsd,
      resolveDwsProjectServiceCategory,
      resolveOwnExpenseProgramName: useOwnExpenseProgramResolverStore().state.resolveOwnExpenseProgramName,
      resolveServiceOption
    }
  }
})
</script>
