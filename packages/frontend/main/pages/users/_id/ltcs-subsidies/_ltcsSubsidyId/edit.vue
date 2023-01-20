<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-ltcs-subsidy-form
      button-text="保存"
      data-form
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
import { ltcsSubsidyStateKey } from '~/composables/stores/use-ltcs-subsidy-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { UserLtcsSubsidy } from '~/models/user-ltcs-subsidy'
import { LtcsSubsidiesApi } from '~/services/api/ltcs-subsidies-api'

type Form = Partial<LtcsSubsidiesApi.Form>

export default defineComponent({
  name: 'SubsidyEditPage',
  middleware: [auth(Permission.updateUserLtcsSubsidies)],
  setup () {
    const { $api } = usePlugins()
    const { user } = useInjected(userStateKey)
    const { ltcsSubsidy } = useInjected(ltcsSubsidyStateKey)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const createFormValue = (x: UserLtcsSubsidy): Form => ({
      defrayerCategory: x.defrayerCategory,
      period: {
        start: x.period.start,
        end: x.period.end
      },
      defrayerNumber: x.defrayerNumber,
      recipientNumber: x.recipientNumber,
      benefitRate: x.benefitRate,
      copay: x.copay
    })
    return {
      ...useBreadcrumbs('users.ltcsSubsidies.edit', user, ltcsSubsidy),
      errors,
      progress,
      user,
      value: createFormValue(ltcsSubsidy.value!),
      submit: (form: Form) => {
        const id = ltcsSubsidy.value!.id
        const userId = user.value!.id
        return updateUserDependant({
          dependant: '公費情報',
          userId,
          callback: () => $api.ltcsSubsidies.update({ form, id, userId }),
          hash: 'ltcs'
        })
      }
    }
  },
  head: () => ({
    title: '利用者介護保険サービス公費情報を編集'
  })
})
</script>
