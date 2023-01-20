<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-dws-calc-spec-form
      button-text="保存"
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
import { userDwsCalcSpecStateKey } from '~/composables/stores/use-user-dws-calc-spec-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { UserDwsCalcSpec } from '~/models/user-dws-calc-spec'
import { UserDwsCalcSpecsApi } from '~/services/api/user-dws-calc-specs-api'

type Form = Partial<UserDwsCalcSpecsApi.Form>

export default defineComponent({
  name: 'UserDwsCalcSpecsEditPage',
  middleware: [auth(Permission.updateUserDwsCalcSpecs)],
  setup () {
    const { $api } = usePlugins()
    const { dwsCalcSpec } = useInjected(userDwsCalcSpecStateKey)
    const { user } = useInjected(userStateKey)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const createFormValue = (x: UserDwsCalcSpec): Form => ({
      effectivatedOn: x.effectivatedOn,
      locationAddition: x.locationAddition
    })
    return {
      ...useBreadcrumbs('users.dwsCalcSpecs.edit', user),
      errors,
      user,
      progress,
      value: createFormValue(dwsCalcSpec.value!),
      submit: (form: Form) => {
        const userId = user.value!.id
        const id = +dwsCalcSpec.value!.id
        return updateUserDependant({
          dependant: '障害福祉サービス利用者別算定情報',
          userId,
          callback: async () => {
            await $api.userDwsCalcSpecs.update({ form, id, userId })
          },
          hash: 'dws'
        })
      }
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス利用者別算定情報を編集'
  })
})
</script>
