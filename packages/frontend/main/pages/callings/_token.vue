<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page class="page-dashboard-index" compact :breadcrumbs="breadcrumbs">
    <div class="z-my-shifts-card">
      <template v-if="isResolved && !isLoadingUsers">
        <v-alert
          v-if="todayServices.length !== 0 && lessThanOneHour(firstService.schedule.start)"
          type="error"
        >
          出勤確認がサービス開始1時間前までに完了しませんでした。至急、上長へご連絡ください。<br>
          <br>
          ご連絡の際は、
          [お名前：{{ loginUserName }}]、
          [時間：{{ time(firstService.schedule.start, 'HH:mm') }} - {{ time(firstService.schedule.end, 'HH:mm') }}]、
          [利用者名:{{ resolveUserName(firstService.userId, '（未定）') }}]
          を記載の上、出勤する旨お知らせください。
        </v-alert>
        <v-alert v-else type="success">
          <span>出勤確認が完了しました。</span>
        </v-alert>
      </template>
      <v-card>
        <transition mode="out-in" name="fade">
          <div v-if="isResolved" key="resolved" class="py-3">
            <v-subheader class="subtitle-1">
              <span class="font-weight-bold">今日のサービス</span>
            </v-subheader>
            <v-card-text v-if="todayServices.length !== 0" class="pt-0">
              <z-shift-list :value="todayServices" />
            </v-card-text>
            <v-card-text v-else class="pt-0">
              <span class="z-my-shifts-card_no-data">勤務シフトはありません。</span>
            </v-card-text>
          </div>
          <v-card-text v-else key="not-resolved" class="text-center">
            <v-progress-circular color="secondary" indeterminate size="64" />
          </v-card-text>
        </transition>
      </v-card>
    </div>
  </z-page>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { sessionStateKey } from '~/composables/stores/use-session-store'
import { time } from '~/composables/time'
import { useAsync } from '~/composables/use-async'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUsers } from '~/composables/use-users'
import { DateLike } from '~/models/date'
import { NuxtContext } from '~/models/nuxt'
import { $datetime } from '~/services/datetime-service'

export default defineComponent({
  name: 'CallingsViewPage',
  validate ({ params }: NuxtContext) {
    return /^[a-zA-Z0-9]{60}$/.test(params.token)
  },
  setup () {
    const { $api, $route, $useFetch } = usePlugins()
    const { auth } = useInjected(sessionStateKey)
    const loginUserName = computed(() => auth.value?.staff.name.displayName)
    const { isResolved, resolvedValue } = useAsync(() => $api.callings.getIndex($route.params.token))
    const firstService = computed(() => resolvedValue.value?.list[0])
    const todayServices = computed(() => resolvedValue.value?.list)
    const lessThanOneHour = (startTime: DateLike) => $datetime.parse(startTime) < $datetime.now.plus({ hours: 1 })

    $useFetch(() => $api.callings.acknowledge($route.params.token))

    return {
      ...useBreadcrumbs('callings'),
      ...useUsers({ permission: Permission.listShifts }),
      firstService,
      isResolved,
      lessThanOneHour,
      loginUserName,
      time,
      todayServices
    }
  },
  head: () => ({
    title: 'スタッフ出勤確認'
  })
})
</script>
