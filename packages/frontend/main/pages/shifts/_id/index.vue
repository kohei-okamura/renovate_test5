<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-shift-view :shift="shift" />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateShifts]) && !shift.isCanceled"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        :icon="$icons.edit"
        @click.prevent="onClickEdit"
      >
        勤務シフトを編集
      </z-fab-speed-dial-button>
      <z-fab-speed-dial-button
        :icon="$icons.delete"
        @click.prevent="onClickCancel"
      >
        勤務シフトをキャンセル
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
    <z-cancel-confirm-dialog
      v-if="!shift.isCanceled"
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
import { shiftStateKey } from '~/composables/stores/use-shift-store'
import { useAuth } from '~/composables/use-auth'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'ShiftsViewPage',
  middleware: [auth(Permission.viewShifts)],
  setup () {
    const { $alert, $api, $datetime, $router, $snackbar } = usePlugins()
    const { isAuthorized, permissions } = useAuth()
    const { progress, withAxios } = useAxios()
    const { shift } = useInjected(shiftStateKey)
    const data = reactive({
      active: false
    })
    const exec = (actionName: string, onSuccess: () => void) => {
      const start = shift.value?.schedule?.start
      if (start !== undefined && $datetime.parse(start) < $datetime.now) {
        $snackbar.error(`過去の勤務シフトは${actionName}できません。`)
      } else {
        onSuccess()
      }
    }
    // 編集
    const onClickEdit = () => {
      exec('編集', () => { $router.push(`/shifts/${shift.value!.id}/edit`) })
    }
    // キャンセル
    const onClickCancel = () => {
      exec('キャンセル', () => { data.active = true })
    }
    const onClickPositive = (reason: string) => {
      exec('キャンセル', () => {
        withAxios(
          async () => {
            data.active = false
            await $api.shifts.cancel({ id: shift.value!.id, reason })
            await catchErrorStack(() => $router.replace('/shifts?restore=1'))
            $snackbar.success('勤務シフトをキャンセルしました。')
          },
          _ => $alert.error('勤務シフトのキャンセルに失敗しました。')
        )
      })
    }
    return {
      ...useBreadcrumbs('shifts.view'),
      data,
      isAuthorized,
      permissions,
      shift,
      onClickCancel,
      onClickEdit,
      onClickNegative: () => {
        data.active = false
      },
      onClickPositive,
      progress
    }
  },
  head: () => ({
    title: '勤務シフト詳細'
  })
})
</script>
