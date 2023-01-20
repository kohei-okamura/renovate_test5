<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-visiting-care-for-pwsd-calc-spec-form
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
import { useCreateOfficeDependant } from '~/composables/use-create-office-dependant'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { VisitingCareForPwsdCalcSpecsApi } from '~/services/api/visiting-care-for-pwsd-calc-specs-api'

type Form = Partial<VisitingCareForPwsdCalcSpecsApi.Form>

export default defineComponent({
  name: 'VisitingCareForPwsdCalcSpecNewPage',
  middleware: [auth(Permission.updateInternalOffices, Permission.updateExternalOffices)],
  setup () {
    const { $api } = usePlugins()
    const { office } = useInjected(officeStateKey)
    const { createOfficeDependant, errors, progress } = useCreateOfficeDependant()
    return {
      ...useBreadcrumbs('offices.visitingCareForPwsdCalcSpecs.new', office),
      errors,
      progress,
      office,
      value: {},
      submit: (form: Form) => {
        const officeId = office.value!.id
        return createOfficeDependant({
          dependant: '算定情報（障害・重度訪問介護）',
          officeId,
          callback: () => $api.visitingCareForPwsdCalcSpecs.create({ form, officeId }),
          hash: 'calc-specs'
        })
      }
    }
  },
  head: () => ({
    title: '事業所算定情報（障害・重度訪問介護）を登録'
  })
})
</script>
