<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-attendance-view :attendance="attendance" />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateAttendances]) && !attendance.isCanceled"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        :icon="$icons.edit"
        :to="`/attendances/${attendance.id}/edit`"
      >
        勤務実績を編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        :icon="$icons.delete"
        @click.prevent="onClickCancel"
      >
        勤務実績をキャンセル
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
    <z-cancel-confirm-dialog
      v-if="!attendance.isCanceled"
      :active="data.active"
      :in-progress="progress"
      @click:negative="onClickNegative"
      @click:positive="onClickPositive"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent, reactive } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { attendanceStateKey } from '~/composables/stores/use-attendance-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'AttendancesViewPage',
  middleware: [auth(Permission.viewAttendances)],
  setup () {
    const { $alert, $api, $router, $snackbar } = usePlugins()
    const { isAuthorized, permissions } = useAuth()
    const { progress, withAxios } = useAxios()
    const { attendance } = useInjected(attendanceStateKey)
    const data = reactive({
      active: false
    })
    const onClickPositive = (reason: string) => withAxios(
      async () => {
        data.active = false
        await $api.attendances.cancel({ id: attendance.value!.id, reason })
        await catchErrorStack(() => $router.replace('/attendances?restore=1'))
        $snackbar.success('勤務実績をキャンセルしました。')
      },
      _ => $alert.error('勤務実績のキャンセルに失敗しました。')
    )
    return {
      ...useBreadcrumbs('attendances.view'),
      data,
      isAuthorized,
      permissions,
      attendance,
      onClickCancel: () => {
        data.active = true
      },
      onClickNegative: () => {
        data.active = false
      },
      onClickPositive,
      progress
    }
  },
  head: () => ({
    title: '勤務実績詳細'
  })
})
</script>
