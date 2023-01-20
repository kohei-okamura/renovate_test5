<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-project-form
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
import { DwsProjectsApi } from '~/services/api/dws-projects-api'

type Form = Partial<DwsProjectsApi.Form>

export default defineComponent({
  name: 'DwsProjectNewPage',
  middleware: [auth(Permission.createDwsProjects)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    const ownExpenseStore = useOwnExpenseProgramResolverStore()
    provide(ownExpenseProgramResolverStateKey, ownExpenseStore.state)
    const submit = (form: Form) => {
      const userId = user.value!.id
      return createUserDependant({
        dependant: '障害福祉サービス計画情報',
        userId,
        callback: () => $api.dwsProjects.create({ form, userId }),
        hash: 'dws'
      })
    }
    return {
      ...useBreadcrumbs('users.dwsProjects.new', user),
      errors,
      permission: Permission.createDwsProjects,
      progress,
      submit,
      user,
      value: {}
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス計画を登録'
  })
})
</script>
