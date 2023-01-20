<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-project-form
      button-text="保存"
      :errors="errors"
      :permission="permission"
      :progress="progress"
      :user="user"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent, provide } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dwsProjectStateKey, dwsProjectStoreKey } from '~/composables/stores/use-dws-project-store'
import {
  ownExpenseProgramResolverStateKey,
  useOwnExpenseProgramResolverStore
} from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useInjected } from '~/composables/use-injected'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { DwsProject } from '~/models/dws-project'
import { DwsProjectsApi } from '~/services/api/dws-projects-api'

type Form = Partial<DwsProjectsApi.Form>

export default defineComponent({
  name: 'DwsProjectEditPage',
  middleware: [auth(Permission.updateDwsProjects)],
  setup () {
    const { user } = useInjected(userStateKey)
    const { dwsProject } = useInjected(dwsProjectStateKey)
    const dwsProjectStore = useInjected(dwsProjectStoreKey)
    const ownExpenseStore = useOwnExpenseProgramResolverStore()
    provide(ownExpenseProgramResolverStateKey, ownExpenseStore.state)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const createFormValue = (x: DwsProject): Form => ({
      effectivatedOn: x.effectivatedOn,
      objective: x.objective,
      officeId: x.officeId,
      programs: x.programs,
      requestFromFamily: x.requestFromFamily,
      requestFromUser: x.requestFromUser,
      staffId: x.staffId,
      writtenOn: x.writtenOn
    })
    const submit = (form: Form) => {
      const id = dwsProject.value!.id
      const userId = user.value!.id
      return updateUserDependant({
        dependant: '障害福祉サービス計画情報',
        userId,
        callback: () => dwsProjectStore.update({ form, id, userId }),
        hash: 'dws'
      })
    }
    return {
      ...useBreadcrumbs('users.dwsProjects.edit', user, dwsProject),
      errors,
      dwsProject,
      permission: Permission.updateDwsProjects,
      progress,
      submit,
      user,
      value: createFormValue(dwsProject.value!)
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス計画を編集'
  })
})
</script>
