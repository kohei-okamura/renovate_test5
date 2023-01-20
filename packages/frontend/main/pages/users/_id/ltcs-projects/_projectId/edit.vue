<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-ltcs-project-form
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
import { ltcsProjectStateKey, ltcsProjectStoreKey } from '~/composables/stores/use-ltcs-project-store'
import {
  ownExpenseProgramResolverStateKey,
  useOwnExpenseProgramResolverStore
} from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useInjected } from '~/composables/use-injected'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { LtcsProject } from '~/models/ltcs-project'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'

type Form = Partial<LtcsProjectsApi.Form>

export default defineComponent({
  name: 'LtcsProjectEditPage',
  middleware: [auth(Permission.updateLtcsProjects)],
  setup () {
    const { user } = useInjected(userStateKey)
    const { ltcsProject } = useInjected(ltcsProjectStateKey)
    const ltcsProjectStore = useInjected(ltcsProjectStoreKey)
    const ownExpenseStore = useOwnExpenseProgramResolverStore()
    provide(ownExpenseProgramResolverStateKey, ownExpenseStore.state)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const createFormValue = (x: LtcsProject): Form => ({
      effectivatedOn: x.effectivatedOn,
      longTermObjective: x.longTermObjective,
      officeId: x.officeId,
      problem: x.problem,
      programs: x.programs,
      requestFromFamily: x.requestFromFamily,
      requestFromUser: x.requestFromUser,
      shortTermObjective: x.shortTermObjective,
      staffId: x.staffId,
      writtenOn: x.writtenOn
    })
    const submit = (form: Form) => {
      const id = ltcsProject.value!.id
      const userId = user.value!.id
      return updateUserDependant({
        dependant: '介護保険サービス計画情報',
        userId,
        callback: () => ltcsProjectStore.update({ form, id, userId }),
        hash: 'ltcs'
      })
    }
    return {
      ...useBreadcrumbs('users.ltcsProjects.edit', user, ltcsProject),
      errors,
      ltcsProject,
      permission: Permission.updateLtcsProjects,
      progress,
      user,
      submit,
      value: createFormValue(ltcsProject.value!)
    }
  },
  head: () => ({
    title: '利用者介護保険サービス計画を編集'
  })
})
</script>
