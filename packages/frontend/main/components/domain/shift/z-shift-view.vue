<!--
  - Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <div class="z-shift-view">
    <z-data-card title="基本情報">
      <z-data-card-item data-status label="状態" :icon="statusIcon">
        {{ resolveStatus(shift) }}
      </z-data-card-item>
      <z-data-card-item v-if="shift.isCanceled" data-cancel-reason label="キャンセル理由">
        {{ shift.reason }}
      </z-data-card-item>
      <z-data-card-item label="勤務シフト区分" :icon="$icons.category">
        <z-task-marker :task="shift.task" />
      </z-data-card-item>
      <z-data-card-item
        v-if="isLongTermCareService"
        label="サービスコード"
        :icon="$icons.serviceCode"
        :value="shift.serviceCode || '-'"
      />
      <z-data-card-item label="事業所" :icon="$icons.office" :value="resolveOfficeAbbr(shift.officeId)" />
      <z-data-card-item v-if="isTaskOnUsers" data-users-card-item label="利用者" :icon="$icons.user">
        <nuxt-link v-if="isAuthorized([permissions.viewUsers])" :to="userLink">
          {{ resolveUserName(shift.userId) }}
        </nuxt-link>
        <template v-else>{{ resolveUserName(shift.userId) }}</template>
      </z-data-card-item>
      <z-data-card-item data-staffs-card-item label="管理スタッフ" :icon="$icons.staff">
        <nuxt-link v-if="isAuthorized([permissions.viewStaffs])" :to="staffLink(shift.assignerId)">
          {{ resolveStaffName(shift.assignerId) }}
        </nuxt-link>
        <template v-else>{{ resolveStaffName(shift.assignerId) }}</template>
      </z-data-card-item>
      <z-data-card-item v-for="x in assignees" :key="x.key" :label="x.label">
        <span v-if="x.isUndecided">未定</span>
        <nuxt-link v-else :to="staffLink(x.staffId)">{{ resolveStaffName(x.staffId) }}</nuxt-link>
        <span v-if="x.isTraining">（研修）</span>
      </z-data-card-item>
      <z-data-card-item label="備考" :icon="$icons.note" :value="shift.note" />
    </z-data-card>
    <z-data-card title="勤務日・勤務時間">
      <z-data-card-item label="勤務日・時間" :icon="$icons.schedule">
        <z-era-date :value="shift.schedule.date" />
        <span>{{ localeDate(shift.schedule.date, { weekday: 'narrow' }) }}</span>
        <z-time :value="shift.schedule.start" />
        <span>-</span>
        <z-time :value="shift.schedule.end" />
      </z-data-card-item>
      <z-data-card-item v-for="(x, i) in shift.durations" :key="i" :label="activity(x.activity)">
        <span>{{ duration(x.duration) }}</span>
      </z-data-card-item>
    </z-data-card>
    <z-data-card title="各種オプション">
      <z-data-card-item label="各種オプション" :icon="$icons.serviceOption">
        <v-chip v-for="x in shift.options" :key="x" label small>{{ resolveServiceOption(x) }}</v-chip>
      </z-data-card-item>
    </z-data-card>
    <z-system-meta-card :id="shift.id" :created-at="shift.createdAt" :updated-at="shift.updatedAt" />
  </div>
</template>

<script lang="ts">
import { computed, defineComponent } from '@nuxtjs/composition-api'
import { resolveActivity } from '@zinger/enums/lib/activity'
import { Permission } from '@zinger/enums/lib/permission'
import { resolveServiceOption } from '@zinger/enums/lib/service-option'
import { range } from '@zinger/helpers'
import { duration } from '~/composables/duration'
import { localeDate } from '~/composables/locale-date'
import { useAuth } from '~/composables/use-auth'
import { useOffices } from '~/composables/use-offices'
import { useShiftStatusIcon } from '~/composables/use-shift-status-icon'
import { useStaffs } from '~/composables/use-staffs'
import { useUsers } from '~/composables/use-users'
import { Shift } from '~/models/shift'
import { StaffId } from '~/models/staff'
import { isLongTermCareService, isOthers } from '~/models/task-utils'

type Props = Readonly<{
  shift: Shift
}>

export default defineComponent<Props>({
  name: 'ZShiftView',
  props: {
    shift: { type: Object, required: true }
  },
  setup: (props: Props) => ({
    ...useAuth(),
    ...useOffices({ permission: Permission.viewShifts }),
    ...useShiftStatusIcon(computed(() => props.shift)),
    ...useStaffs({ permission: Permission.viewShifts }),
    ...useUsers({ permission: Permission.viewShifts }),
    activity: resolveActivity,
    assignees: computed(() => {
      const shift = props.shift
      return range(1, shift.headcount).map((n, i) => ({
        ...shift.assignees[i],
        key: n,
        label: `担当スタッフ（${n}人目）`
      }))
    }),
    duration,
    isLongTermCareService: computed(() => isLongTermCareService(props.shift.task)),
    isTaskOnUsers: computed(() => !isOthers(props.shift.task)),
    localeDate,
    resolveServiceOption,
    resolveStatus: (x: Shift) => x.isCanceled ? 'キャンセル' : x.isConfirmed ? '確定' : '未確定',
    staffLink: (id: StaffId) => `/staffs/${id}`,
    userLink: computed(() => `/users/${props.shift.userId}`)
  })
})
</script>
