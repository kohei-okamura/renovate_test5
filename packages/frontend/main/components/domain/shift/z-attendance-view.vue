<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div class="z-attendance-view">
    <z-data-card title="基本情報">
      <z-data-card-item data-status label="状態" :icon="statusIcon">
        {{ resolveStatus(attendance) }}
      </z-data-card-item>
      <z-data-card-item v-if="attendance.isCanceled" data-cancel-reason label="キャンセル理由">
        {{ attendance.reason }}
      </z-data-card-item>
      <z-data-card-item label="勤務実績区分" :icon="$icons.category">
        <z-task-marker :task="attendance.task" />
      </z-data-card-item>
      <z-data-card-item
        v-if="isLongTermCareService"
        label="サービスコード"
        :icon="$icons.serviceCode"
        :value="attendance.serviceCode || '-'"
      />
      <z-data-card-item label="事業所" :icon="$icons.office" :value="resolveOfficeAbbr(attendance.officeId)" />
      <z-data-card-item v-if="isTaskOnUsers" data-user-card-item label="利用者" :icon="$icons.user">
        <nuxt-link v-if="isAuthorized([permissions.viewUsers])" :to="userLink">
          {{ resolveUserName(attendance.userId) }}
        </nuxt-link>
        <template v-else>{{ resolveUserName(attendance.userId) }}</template>
      </z-data-card-item>
      <z-data-card-item data-staff-card-item label="管理スタッフ" :icon="$icons.staff">
        <nuxt-link v-if="isAuthorized([permissions.viewStaffs])" :to="staffLink(attendance.assignerId)">
          {{ resolveStaffName(attendance.assignerId) }}
        </nuxt-link>
        <template v-else>{{ resolveStaffName(attendance.assignerId) }}</template>
      </z-data-card-item>
      <z-data-card-item v-for="x in assignees" :key="x.key" :label="x.label">
        <span v-if="x.isUndecided">未定</span>
        <nuxt-link v-else :to="staffLink(x.staffId)">{{ resolveStaffName(x.staffId) }}</nuxt-link>
        <span v-if="x.isTraining">（研修）</span>
      </z-data-card-item>
      <z-data-card-item label="備考" :icon="$icons.note" :value="attendance.note" />
    </z-data-card>
    <z-data-card title="勤務日・勤務時間">
      <z-data-card-item label="勤務日・時間" :icon="$icons.schedule">
        <z-era-date :value="attendance.schedule.date" />
        <span>{{ localeDate(attendance.schedule.date, { weekday: 'narrow' }) }}</span>
        <z-time :value="attendance.schedule.start" />
        <span>-</span>
        <z-time :value="attendance.schedule.end" />
      </z-data-card-item>
      <z-data-card-item v-for="(x, i) in attendance.durations" :key="i" :label="activity(x.activity)">
        <span>{{ duration(x.duration) }}</span>
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="各種オプション">
      <z-data-card-item label="各種オプション" :icon="$icons.serviceOption">
        <v-chip v-for="x in attendance.options" :key="x" label small>{{ resolveServiceOption(x) }}</v-chip>
      </z-data-card-item>
    </z-data-card>
    <z-system-meta-card :id="attendance.id" :created-at="attendance.createdAt" :updated-at="attendance.updatedAt" />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent, toRefs } from '@nuxtjs/composition-api'
import { resolveActivity } from '@zinger/enums/lib/activity'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveServiceOption } from '@zinger/enums/lib/service-option'
import { range } from '@zinger/helpers'
import { duration } from '~/composables/duration'
import { localeDate } from '~/composables/locale-date'
import { useAttendanceStatusIcon } from '~/composables/use-attendance-status-icon'
import { useAuth } from '~/composables/use-auth'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { Attendance } from '~/models/attendance'
import { StaffId } from '~/models/staff'
import { isLongTermCareService, isOthers } from '~/models/task-utils'

type Props = Readonly<{
  attendance: Attendance
}>

export default defineComponent<Props>({
  name: 'ZAttendanceView',
  props: {
    attendance: { type: Object, required: true }
  },
  setup (props: Props) {
    const propRefs = toRefs(props)
    return {
      ...useAttendanceStatusIcon(propRefs.attendance),
      ...useAuth(),
      ...useOffices({ permission: Permission.viewAttendances }),
      ...useStaffs({ permission: Permission.viewAttendances }),
      ...useUsers({ permission: Permission.viewAttendances }),
      activity: resolveActivity,
      assignees: computed(() => {
        const attendance = props.attendance
        return range(1, attendance.headcount).map((n, i) => ({
          ...attendance.assignees[i],
          key: n,
          label: `担当スタッフ（${n}人目）`
        }))
      }),
      duration,
      isLongTermCareService: computed(() => isLongTermCareService(props.attendance.task)),
      isTaskOnUsers: computed(() => !isOthers(props.attendance.task)),
      localeDate,
      resolveServiceOption,
      resolveStatus: (x: Attendance) => x.isCanceled ? 'キャンセル' : x.isConfirmed ? '確定' : '未確定',
      staffLink: (id: StaffId) => `/staffs/${id}`,
      userLink: computed(() => `/users/${props.attendance.userId}`)
    }
  }
})
</script>
