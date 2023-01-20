<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-attendance-form
      button-text="保存"
      :errors="errors"
      :permission="permission"
      :progress="progress"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { datetime } from '~/composables/datetime'
import { attendanceStoreKey } from '~/composables/stores/use-attendance-store'
import { time } from '~/composables/time'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { Attendance } from '~/models/attendance'
import { ISO_DATE_FORMAT, ISO_TIME_FORMAT } from '~/models/date'
import { AttendancesApi } from '~/services/api/attendances-api'

type Form = Partial<AttendancesApi.Form>

export default defineComponent({
  name: 'AttendancesEditPage',
  middleware: [auth(Permission.updateAttendances)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const attendanceStore = useInjected(attendanceStoreKey)
    const { attendance } = attendanceStore.state
    const createFormValue = (x: Attendance): Form => ({
      task: x.task,
      serviceCode: x.serviceCode,
      officeId: x.officeId,
      userId: x.userId,
      contractId: x.contractId,
      assignerId: x.assignerId,
      assignees: x.assignees,
      headcount: x.headcount,
      schedule: {
        date: datetime(x.schedule.date, ISO_DATE_FORMAT),
        start: time(x.schedule.start, ISO_TIME_FORMAT),
        end: time(x.schedule.end, ISO_TIME_FORMAT)
      },
      durations: [...x.durations],
      options: [...x.options],
      note: x.note
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('attendances.edit', attendance),
      errors,
      permission: Permission.updateAttendances,
      progress,
      value: createFormValue(attendance.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = attendance.value!.id
          await attendanceStore.update({ form, id })
          await catchErrorStack(() => $router.replace(`/attendances/${id}`))
          $snackbar.success('勤務実績を編集しました。')
        }),
        (error: Error) => $alert.error('勤務実績の編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '勤務実績を編集'
  })
})
</script>
