<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-home-visit-long-term-care-calc-spec-form
      button-text="登録"
      :errors="errors"
      :office="office"
      :progress="progress"
      :value="value"
      @submit="submit"
    />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { officeStateKey } from '~/composables/stores/use-office-store'
import { useHomeVisitLongTermCareCalcSpecDependant } from '~/composables/use-home-visit-long-term-care-calc-spec-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'

type Form = Partial<HomeVisitLongTermCareCalcSpecsApi.Form>

export default defineComponent({
  name: 'HomeVisitLongTermCareCalcSpecNewPage',
  middleware: [auth(Permission.updateInternalOffices, Permission.updateExternalOffices)],
  setup () {
    const { $api } = usePlugins()
    const { office } = useInjected(officeStateKey)
    const {
      errors,
      progress,
      createHomeVisitLongTermCareCalcSpecDependant
    } = useHomeVisitLongTermCareCalcSpecDependant()
    return {
      ...useBreadcrumbs('offices.homeVisitLongTermCareCalcSpecs.new', office),
      errors,
      office,
      progress,
      submit: (form: Form) => {
        const officeId = office.value!.id
        createHomeVisitLongTermCareCalcSpecDependant({
          editOrRegistration: '登録',
          officeId,
          callback: () => $api.homeVisitLongTermCareCalcSpecs.create({ form, officeId })
        })
      },
      value: {}
    }
  },
  head: () => ({
    title: '事業所算定情報（介保・訪問介護）を登録'
  })
})
</script>
