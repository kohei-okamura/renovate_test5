<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-contract-form
      button-text="登録"
      :errors="errors"
      :permission="permission"
      :progress="progress"
      :service-segment="segment"
      :user="user"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { ServiceSegment } from '@zinger/enums/lib/service-segment'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useCreateUserDependant } from '~/composables/use-create-user-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { LtcsContractsApi } from '~/services/api/ltcs-contracts-api'

type Form = Partial<LtcsContractsApi.CreateForm>

export default defineComponent({
  name: 'LtcsContractsNewPage',
  middleware: [auth(Permission.createLtcsContracts)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { createUserDependant, errors, progress } = useCreateUserDependant()
    const submit = (form: Form) => {
      const userId = user.value!.id
      return createUserDependant({
        dependant: '契約情報',
        userId,
        callback: () => $api.ltcsContracts.create({ form, userId }),
        hash: 'ltcs'
      })
    }
    const value: Form = {
      officeId: undefined,
      note: ''
    }
    return {
      ...useBreadcrumbs('users.ltcsContracts.new', user),
      errors,
      permission: Permission.createLtcsContracts,
      progress,
      segment: ServiceSegment.longTermCare,
      submit,
      user,
      value
    }
  },
  head: () => ({
    title: '利用者介護保険サービス契約を登録'
  })
})
</script>
