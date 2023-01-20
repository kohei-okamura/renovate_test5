<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-home-visit-long-term-care-calc-spec-form
      button-text="保存"
      data-form
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
import { homeVisitLongTermCareCalcSpecStateKey } from '~/composables/stores/use-home-visit-long-term-care-calc-spec-store'
import { officeStateKey } from '~/composables/stores/use-office-store'
import { useHomeVisitLongTermCareCalcSpecDependant } from '~/composables/use-home-visit-long-term-care-calc-spec-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { HomeVisitLongTermCareCalcSpec } from '~/models/home-visit-long-term-care-calc-spec'
import { HomeVisitLongTermCareCalcSpecsApi } from '~/services/api/home-visit-long-term-care-calc-specs-api'

type Form = Partial<HomeVisitLongTermCareCalcSpecsApi.Form>

export default defineComponent({
  name: 'HomeVisitLongTermCareCalcSpecsEditPage',
  middleware: [auth(Permission.updateInternalOffices, Permission.updateExternalOffices)],
  setup () {
    const { $api } = usePlugins()
    const { office } = useInjected(officeStateKey)
    const { homeVisitLongTermCareCalcSpec } = useInjected(homeVisitLongTermCareCalcSpecStateKey)
    const {
      errors,
      progress,
      createHomeVisitLongTermCareCalcSpecDependant
    } = useHomeVisitLongTermCareCalcSpecDependant()
    const createFormValue = (x: HomeVisitLongTermCareCalcSpec): Form => ({
      baseIncreaseSupportAddition: x.baseIncreaseSupportAddition,
      locationAddition: x.locationAddition,
      period: {
        start: x.period?.start,
        end: x.period?.end
      },
      specifiedOfficeAddition: x.specifiedOfficeAddition,
      specifiedTreatmentImprovementAddition: x.specifiedTreatmentImprovementAddition,
      treatmentImprovementAddition: x.treatmentImprovementAddition
    })
    return {
      ...useBreadcrumbs('offices.homeVisitLongTermCareCalcSpecs.edit', office),
      errors,
      office,
      progress,
      value: createFormValue(homeVisitLongTermCareCalcSpec.value!),
      submit: (form: Form) => {
        const officeId = office.value!.id
        const id = +homeVisitLongTermCareCalcSpec.value!.id
        createHomeVisitLongTermCareCalcSpecDependant({
          editOrRegistration: '編集',
          officeId,
          callback: () => $api.homeVisitLongTermCareCalcSpecs.update({ form, id, officeId })
        })
      }
    }
  },
  head: () => ({
    title: '事業所算定情報（介保・訪問介護）を編集'
  })
})
</script>
