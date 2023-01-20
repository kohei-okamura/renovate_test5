<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-summary :user="user" />
    <z-data-card title="提供者情報">
      <z-data-card-item label="事業所名" :icon="$icons.office" :value="resolveOfficeAbbr(dwsProject.officeId)" />
      <z-data-card-item
        label="計画作成者 (サ責)"
        :icon="$icons.staff"
        :value="resolveStaffName(dwsProject.staffId)"
      />
    </z-data-card>
    <z-data-card title="援助目標">
      <z-data-card-item label="ご本人の希望" :icon="$icons.request" :value="dwsProject.requestFromUser" />
      <z-data-card-item label="ご家族の希望" :icon="$icons.blank" :value="dwsProject.requestFromFamily" />
      <z-data-card-item label="援助目標" :icon="$icons.objective" :value="dwsProject.objective" />
    </z-data-card>
    <z-dws-project-weekly-services-card v-for="(program, i) in dwsProject.programs" :key="i" :program="program" />
    <z-system-meta-card
      :id="dwsProject.id"
      :created-at="dwsProject.createdAt"
      :updated-at="dwsProject.updatedAt"
    />
    <z-fab
      v-if="isAuthorized([permissions.updateDwsProjects])"
      bottom
      data-fab
      fixed
      nuxt
      right
      :icon="$icons.edit"
      :to="`/users/${user.id}/dws-projects/${dwsProject.id}/edit`"
    >
      障害福祉サービス計画情報を編集
    </z-fab>
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { resolveDayOfWeek } from '@zinger/enums/lib/day-of-week'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dwsProjectStateKey } from '~/composables/stores/use-dws-project-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useAuth } from '~/composables/use-auth'
import { useInjected } from '~/composables/use-injected'
import { useOffices } from '~/composables/use-offices'
import { useStaffs } from '~/composables/use-staffs'
import { auth } from '~/middleware/auth'

export default defineComponent({
  name: 'DwsProjectViewPage',
  middleware: [auth(Permission.viewDwsProjects)],
  setup () {
    const { dwsProject } = useInjected(dwsProjectStateKey)
    const { user } = useInjected(userStateKey)
    return {
      ...useAuth(),
      ...useBreadcrumbs('users.dwsProjects.view', user),
      ...useOffices({ permission: Permission.viewDwsProjects }),
      ...useStaffs({ permission: Permission.viewDwsProjects }),
      dwsProject,
      resolveDayOfWeek,
      user
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス計画詳細'
  })
})
</script>
