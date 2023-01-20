<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-ltcs-calc-spec-form
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
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'

type Form = Partial<UserLtcsCalcSpecsApi.Form>

export default defineComponent({
  name: 'UserLtcsCalcSpecsNewPage',
  middleware: [auth(Permission.createUserLtcsCalcSpecs)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    const submit = (form: Form) => {
      const userId = user.value!.id
      return createUserDependant({
        dependant: '介護保険サービス利用者別算定情報',
        userId,
        callback: () => $api.userLtcsCalcSpecs.create({ form, userId }),
        hash: 'ltcs'
      })
    }
    return {
      ...useBreadcrumbs('users.ltcsCalcSpecs.new', user),
      errors,
      progress,
      submit,
      user,
      value: {}
    }
  },
  head: () => ({
    title: '利用者介護保険サービス利用者別算定情報を登録'
  })
})
</script>
