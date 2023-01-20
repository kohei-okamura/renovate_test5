<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-subsidy-form
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
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'

type Form = Partial<DwsSubsidiesApi.Form>

export default defineComponent({
  name: 'DwsSubsidyNewPage',
  middleware: [auth(Permission.createUserDwsSubsidies)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    const setZeroValueWhenEmpty = (form: Form) => ({
      ...form,
      benefitRate: form.benefitRate || 0,
      benefitAmount: form.benefitAmount || 0,
      copayAmount: form.copayAmount || 0
    })
    return {
      ...useBreadcrumbs('users.dwsSubsidies.new', user),
      errors,
      progress,
      user,
      value: {},
      submit: (form: Form) => {
        const userId = user.value!.id
        return createUserDependant({
          dependant: '自治体助成情報',
          userId,
          callback: () => $api.dwsSubsidies.create({ form: setZeroValueWhenEmpty(form), userId }),
          hash: 'dws'
        })
      }
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス自治体助成情報を登録'
  })
})
</script>
