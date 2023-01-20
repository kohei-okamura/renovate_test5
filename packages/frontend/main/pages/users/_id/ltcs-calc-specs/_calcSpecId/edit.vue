<!--
  - Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-user-ltcs-calc-spec-form
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
import { userLtcsCalcSpecStateKey } from '~/composables/stores/use-user-ltcs-calc-spec-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { UserLtcsCalcSpec } from '~/models/user-ltcs-calc-spec'
import { UserLtcsCalcSpecsApi } from '~/services/api/user-ltcs-calc-specs-api'

type Form = Partial<UserLtcsCalcSpecsApi.Form>

export default defineComponent({
  name: 'UserLtcsCalcSpecsEditPage',
  middleware: [auth(Permission.updateUserLtcsCalcSpecs)],
  setup () {
    const { $api } = usePlugins()
    const { ltcsCalcSpec } = useInjected(userLtcsCalcSpecStateKey)
    const { user } = useInjected(userStateKey)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const createFormValue = (x: UserLtcsCalcSpec): Form => ({
      effectivatedOn: x.effectivatedOn,
      locationAddition: x.locationAddition
    })
    return {
      ...useBreadcrumbs('users.ltcsCalcSpecs.edit', user),
      errors,
      user,
      progress,
      value: createFormValue(ltcsCalcSpec.value!),
      submit: (form: Form) => {
        const userId = user.value!.id
        const id = +ltcsCalcSpec.value!.id
        return updateUserDependant({
          dependant: '介護保険サービス利用者別算定情報',
          userId,
          callback: async () => {
            await $api.userLtcsCalcSpecs.update({ form, id, userId })
          },
          hash: 'ltcs'
        })
      }
    }
  },
  head: () => ({
    title: '利用者介護保険サービス利用者別算定情報を編集'
  })
})
</script>
