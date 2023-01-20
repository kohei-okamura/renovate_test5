<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-data-card title="提供者情報">
      <z-data-card-item label="事業所名" :icon="$icons.office" :value="resolveOfficeAbbr(ltcsProject.officeId)" />
      <z-data-card-item
        label="計画作成者 (サ責)"
        :icon="$icons.staff"
        :value="resolveStaffName(ltcsProject.staffId)"
      />
    </z-data-card>
    <z-data-card title="援助目標">
      <z-data-card-item label="解決すべき課題" :icon="$icons.problem" :value="ltcsProject.problem" />
      <z-data-card-item label="ご本人の希望" :icon="$icons.request" :value="ltcsProject.requestFromUser" />
      <z-data-card-item label="ご家族の希望" :icon="$icons.blank" :value="ltcsProject.requestFromFamily" />
      <z-data-card-item
        :icon="$icons.objective"
        :label="'長期目標：' + resolveTermObjectives(ltcsProject.longTermObjective.term)"
        :value="ltcsProject.longTermObjective.text"
      />
      <z-data-card-item
        :icon="$icons.blank"
        :label="'短期目標：' + resolveTermObjectives(ltcsProject.shortTermObjective.term)"
        :value="ltcsProject.shortTermObjective.text"
      />
    </z-data-card>
    <z-ltcs-project-weekly-services-card v-for="(program, i) in ltcsProject.programs" :key="i" :program="program" />
    <z-system-meta-card
      :id="ltcsProject.id"
      :created-at="ltcsProject.createdAt"
      :updated-at="ltcsProject.updatedAt"
    />
    <z-fab-speed-dial
      v-if="isAuthorized([permissions.updateLtcsProjects])"
      data-fab
      :icon="$icons.edit"
    >
      <z-fab-speed-dial-button
        nuxt
        :icon="$icons.edit"
        :to="`/users/${user.id}/ltcs-projects/${ltcsProject.id}/edit`"
      >
        介護保険サービス計画情報を編集
      </z-fab-speed-dial-button>
    </z-fab-speed-dial>
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { eraDate } from '~/composables/era-date'
import { ltcsProjectStateKey } from '~/composables/stores/use-ltcs-project-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { auth } from '~/middleware/auth'
import { Range } from '~/models/range'

export default defineComponent({
  name: 'LtcsProjectViewPage',
  middleware: [auth(Permission.viewLtcsProjects)],
  setup () {
    const { ltcsProject } = useInjected(ltcsProjectStateKey)
    const { user } = useInjected(userStateKey)
    return {
      ...useAuth(),
      ...useBreadcrumbs('users.ltcsProjects.view', user),
      ...useOffices({ permission: Permission.viewLtcsProjects }),
      ...useStaffs({ permission: Permission.viewLtcsProjects }),
      ltcsProject,
      resolveDayOfWeek,
      resolveTermObjectives: ({ start, end }: Range<string>) => `${eraDate(start)} ~ ${eraDate(end)}`,
      user
    }
  },
  head: () => ({
    title: '利用者介護保険サービス計画詳細'
  })
})
</script>
