<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-ltcs-subsidy-form
      button-text="登録"
      :errors="errors"
      :progress="progress"
      :user="user"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useCreateUserDependant } from '~/composables/use-create-user-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'

type Form = Partial<LtcsSubsidiesApi.Form>

export default defineComponent({
  name: 'SubsidiesNewPage',
  middleware: [auth(Permission.createUserLtcsSubsidies)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    return {
      ...useBreadcrumbs('users.ltcsSubsidies.new', user),
      errors,
      progress,
      user,
      value: {},
      submit: (form: Form) => {
        const userId = user.value!.id
        return createUserDependant({
          dependant: '公費情報',
          userId,
          callback: () => $api.ltcsSubsidies.create({ form, userId }),
          hash: 'ltcs'
        })
      }
    }
  },
  head: () => ({
    title: '利用者介護保険サービス公費情報を登録'
  })
})
</script>
