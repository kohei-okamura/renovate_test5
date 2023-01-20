<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-ltcs-project-form
      button-text="登録"
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
import {
  ownExpenseProgramResolverStateKey,
  useOwnExpenseProgramResolverStore
} from '~/composables/stores/use-own-expense-program-resolver-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useCreateUserDependant } from '~/composables/use-create-user-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { LtcsProjectsApi } from '~/services/api/ltcs-projects-api'

type Form = Partial<LtcsProjectsApi.Form>

export default defineComponent({
  name: 'LtcsProjectNewPage',
  middleware: [auth(Permission.createLtcsProjects)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    const ownExpenseStore = useOwnExpenseProgramResolverStore()
    provide(ownExpenseProgramResolverStateKey, ownExpenseStore.state)
    const submit = (form: Form) => {
      const userId = user.value!.id
      return createUserDependant({
        dependant: '介護保険サービス計画情報',
        userId,
        callback: () => $api.ltcsProjects.create({ form, userId }),
        hash: 'ltcs'
      })
    }
    return {
      ...useBreadcrumbs('users.ltcsProjects.new', user),
      errors,
      permission: Permission.createLtcsProjects,
      progress,
      submit,
      user,
      value: {}
    }
  },
  head: () => ({
    title: '利用者介護保険サービス計画を登録'
  })
})
</script>
