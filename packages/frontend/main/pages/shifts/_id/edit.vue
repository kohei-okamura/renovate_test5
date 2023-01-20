<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-shift-form
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
import { shiftStateKey, shiftStoreKey } from '~/composables/stores/use-shift-store'
import { time } from '~/composables/time'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { ISO_DATE_FORMAT, ISO_TIME_FORMAT } from '~/models/date'
import { Shift } from '~/models/shift'
import { ShiftsApi } from '~/services/api/shifts-api'

type Form = Partial<ShiftsApi.Form>

export default defineComponent({
  name: 'ShiftsEditPage',
  middleware: [auth(Permission.updateShifts)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const shiftStore = useInjected(shiftStoreKey)
    const { shift } = useInjected(shiftStateKey)
    const createFormValue = (x: Shift): Form => ({
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
      ...useBreadcrumbs('shifts.edit', shift),
      errors,
      permission: Permission.updateShifts,
      progress,
      value: createFormValue(shift.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = shift.value!.id
          await shiftStore.update({ form, id })
          await catchErrorStack(() => $router.replace(`/shifts/${id}`))
          $snackbar.success('勤務シフトを編集しました。')
        }),
        (error: Error) => $alert.error('勤務シフトの編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '勤務シフトを編集'
  })
})
</script>
