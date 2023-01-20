<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-office-form
      button-text="保存"
      :errors="errors"
      :permission="permission"
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
import { catchErrorStack } from '~/composables/catch-error-stack'
import { officeStateKey, officeStoreKey } from '~/composables/stores/use-office-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { Office } from '~/models/office'
import { OfficesApi } from '~/services/api/offices-api'

type Form = Partial<OfficesApi.Form>

export default defineComponent({
  name: 'OfficesEditPage',
  middleware: [auth(Permission.updateInternalOffices, Permission.updateExternalOffices)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { office } = useInjected(officeStateKey)
    const store = useInjected(officeStoreKey)
    const createFormValue = (x: Office): Form => ({
      name: x.name,
      abbr: x.abbr,
      phoneticName: x.phoneticName,
      corporationName: x.corporationName,
      phoneticCorporationName: x.phoneticCorporationName,
      officeGroupId: x.officeGroupId,
      postcode: x.addr.postcode,
      prefecture: x.addr.prefecture,
      city: x.addr.city,
      street: x.addr.street,
      apartment: x.addr.apartment,
      tel: x.tel,
      fax: x.fax,
      email: x.email,
      purpose: x.purpose,
      qualifications: x.qualifications,
      dwsGenericService: x.dwsGenericService,
      dwsCommAccompanyService: x.dwsCommAccompanyService,
      ltcsHomeVisitLongTermCareService: x.ltcsHomeVisitLongTermCareService,
      ltcsCareManagementService: x.ltcsCareManagementService,
      ltcsCompHomeVisitingService: x.ltcsCompHomeVisitingService,
      ltcsPreventionService: x.ltcsPreventionService,
      status: x.status
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('offices.edit', office),
      errors,
      permission: Permission.updateInternalOffices,
      progress,
      value: createFormValue(office.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = office.value!.id
          await store.update({ form, id })
          await catchErrorStack(() => $router.replace(`/offices/${id}`))
          $snackbar.success('事業所情報を編集しました。')
        }),
        (error: Error) => $alert.error('事業所情報の編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: '事業所基本情報を編集'
  })
})
</script>
