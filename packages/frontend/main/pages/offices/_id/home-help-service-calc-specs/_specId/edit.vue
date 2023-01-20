<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-home-help-service-calc-spec-form
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
import { homeHelpServiceCalcSpecStateKey } from '~/composables/stores/use-home-help-service-calc-spec-store'
import { officeStateKey } from '~/composables/stores/use-office-store'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { useUpdateOfficeDependant } from '~/composables/use-update-office-dependant'
import { auth } from '~/middleware/auth'
import { HomeHelpServiceCalcSpec } from '~/models/home-help-service-calc-spec'
import { HomeHelpServiceCalcSpecsApi } from '~/services/api/home-help-service-calc-specs-api'

type Form = Partial<HomeHelpServiceCalcSpecsApi.Form>

export default defineComponent({
  name: 'HomeHelpServiceCalcSpecsEditPage',
  middleware: [auth(Permission.updateInternalOffices, Permission.updateExternalOffices)],
  setup () {
    const { $api } = usePlugins()
    const { office } = useInjected(officeStateKey)
    const { homeHelpServiceCalcSpec } = useInjected(homeHelpServiceCalcSpecStateKey)
    const { errors, progress, updateOfficeDependant } = useUpdateOfficeDependant()
    const createFormValue = (x: HomeHelpServiceCalcSpec): Form => ({
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
      ...useBreadcrumbs('offices.homeHelpServiceCalcSpecs.edit', office),
      errors,
      office,
      progress,
      value: createFormValue(homeHelpServiceCalcSpec.value!),
      submit: (form: Form) => {
        const officeId = office.value!.id
        const id = +homeHelpServiceCalcSpec.value!.id
        return updateOfficeDependant({
          dependant: '算定情報（障害・居宅介護）',
          officeId,
          callback: () => $api.homeHelpServiceCalcSpecs.update({ form, id, officeId }),
          hash: 'calc-specs'
        })
      }
    }
  },
  head: () => ({
    title: '事業所算定情報（障害・居宅介護）を編集'
  })
})
</script>
