<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-dws-calc-spec-form
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
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'

type Form = Partial<UserDwsCalcSpecsApi.Form>

export default defineComponent({
  name: 'UserDwsCalcSpecsNewPage',
  middleware: [auth(Permission.createUserDwsCalcSpecs)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    const submit = (form: Form) => {
      const userId = user.value!.id
      return createUserDependant({
        dependant: '障害福祉サービス利用者別算定情報',
        userId,
        callback: () => $api.userDwsCalcSpecs.create({ form, userId }),
        hash: 'dws'
      })
    }
    return {
      ...useBreadcrumbs('users.dwsCalcSpecs.new', user),
      errors,
      progress,
      submit,
      user,
      value: {}
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス利用者別算定情報を登録'
  })
})
</script>
