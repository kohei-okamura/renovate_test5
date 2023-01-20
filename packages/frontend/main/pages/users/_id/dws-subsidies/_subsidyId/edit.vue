<!--
  - Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-dws-subsidy-form
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
import { Rounding } from '@zinger/enums/lib/rounding'
import { UserDwsSubsidyFactor } from '@zinger/enums/lib/user-dws-subsidy-factor'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { dwsSubsidyStateKey, dwsSubsidyStoreKey } from '~/composables/stores/use-dws-subsidy-store'
import { userStateKey } from '~/composables/stores/use-user-store'
import { useInjected } from '~/composables/use-injected'
import { useUpdateUserDependant } from '~/composables/use-update-user-dependant'
import { auth } from '~/middleware/auth'
import { UserDwsSubsidy } from '~/models/user-dws-subsidy'
import { DwsSubsidiesApi } from '~/services/api/dws-subsidies-api'

type Form = Partial<DwsSubsidiesApi.Form>

export default defineComponent({
  name: 'DwsSubsidyEditPage',
  middleware: [auth(Permission.updateUserDwsSubsidies)],
  setup () {
    const { user } = useInjected(userStateKey)
    const { dwsSubsidy } = useInjected(dwsSubsidyStateKey)
    const dwsSubsidyStore = useInjected(dwsSubsidyStoreKey)
    const { errors, progress, updateUserDependant } = useUpdateUserDependant()
    const createFormValue = (x: UserDwsSubsidy): Form => ({
      period: x.period,
      cityName: x.cityName,
      cityCode: x.cityCode,
      subsidyType: x.subsidyType,
      factor: x.factor === UserDwsSubsidyFactor.none ? undefined : x.factor,
      benefitRate: x.benefitRate || undefined,
      copayRate: x.copayRate || undefined,
      rounding: x.rounding === Rounding.none ? undefined : x.rounding,
      benefitAmount: x.benefitAmount || undefined,
      copayAmount: x.copayAmount || undefined,
      note: x.note
    })
    const setZeroValueWhenEmpty = (form: Form) => ({
      ...form,
      benefitRate: form.benefitRate || 0,
      copayRate: form.copayRate || 0,
      benefitAmount: form.benefitAmount || 0,
      copayAmount: form.copayAmount || 0
    })
    return {
      ...useBreadcrumbs('users.dwsSubsidies.edit', user, dwsSubsidy),
      errors,
      dwsSubsidy,
      progress,
      user,
      value: createFormValue(dwsSubsidy.value!),
      submit: (form: Form) => {
        const id = dwsSubsidy.value!.id
        const userId = user.value!.id
        return updateUserDependant({
          dependant: '自治体助成情報',
          userId,
          callback: () => dwsSubsidyStore.update({ form: setZeroValueWhenEmpty(form), id, userId }),
          hash: 'dws'
        })
      }
    }
  },
  head: () => ({
    title: '利用者障害福祉サービス自治体助成情報を編集'
  })
})
</script>
