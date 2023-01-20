<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-visiting-care-for-pwsd-calc-spec-form
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
import { officeStateKey } from '~/composables/stores/use-office-store'
import { visitingCareForPwsdCalcSpecStateKey } from '~/composables/stores/use-visiting-care-for-pwsd-calc-spec-store'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUpdateOfficeDependant } from '~/composables/use-update-office-dependant'
import { auth } from '~/middleware/auth'
import { VisitingCareForPwsdCalcSpec } from '~/models/visiting-care-for-pwsd-calc-spec'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'

type Form = Partial<VisitingCareForPwsdCalcSpecsApi.Form>

export default defineComponent({
  name: 'VisitingCareForPwsdCalcSpecsEditPage',
  middleware: [auth(Permission.updateInternalOffices, Permission.updateExternalOffices)],
  setup () {
    const { $api } = usePlugins()
    const { office } = useInjected(officeStateKey)
    const { errors, progress, updateOfficeDependant } = useUpdateOfficeDependant()
    const { visitingCareForPwsdCalcSpec } = useInjected(visitingCareForPwsdCalcSpecStateKey)
    const createFormValue = (x: VisitingCareForPwsdCalcSpec): Form => ({
      period: {
        start: x.period?.start,
        end: x.period?.end
      },
      specifiedOfficeAddition: x.specifiedOfficeAddition,
      specifiedTreatmentImprovementAddition: x.specifiedTreatmentImprovementAddition,
      treatmentImprovementAddition: x.treatmentImprovementAddition,
      baseIncreaseSupportAddition: x.baseIncreaseSupportAddition
    })
    return {
      ...useBreadcrumbs('offices.visitingCareForPwsdCalcSpecs.edit', office),
      errors,
      office,
      progress,
      value: createFormValue(visitingCareForPwsdCalcSpec.value!),
      submit: (form: Form) => {
        const officeId = office.value!.id
        const id = +visitingCareForPwsdCalcSpec.value!.id
        return updateOfficeDependant({
          dependant: '算定情報（障害・重度訪問介護）',
          officeId,
          callback: () => $api.visitingCareForPwsdCalcSpecs.update({ form, id, officeId }),
          hash: 'calc-specs'
        })
      }
    }
  },
  head: () => ({
    title: '事業所算定情報（障害・重度訪問介護）を編集'
  })
})
</script>
