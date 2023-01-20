<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <z-staff-edit-form button-text="保存" :errors="errors" :progress="progress" :value="value" @submit="submit" />
  </z-page>
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { useBreadcrumbs } from '~/composables/breadcrumbs'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { staffStateKey, staffStoreKey } from '~/composables/stores/use-staff-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { Staff } from '~/models/staff'
import { StaffsApi } from '~/services/api/staffs-api'

type Form = Partial<StaffsApi.UpdateForm>

export default defineComponent({
  name: 'StaffsEditPage',
  middleware: [auth(Permission.updateStaffs)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { staff } = useInjected(staffStateKey)
    const staffStore = useInjected(staffStoreKey)
    const createFormValue = (x: Staff): Form => ({
      employeeNumber: x.employeeNumber,
      ...x.name,
      sex: x.sex,
      birthday: x.birthday,
      ...x.addr,
      tel: x.tel,
      fax: x.fax,
      email: x.email,
      certifications: x.certifications,
      roleIds: x.roleIds,
      officeIds: x.officeIds,
      officeGroupIds: x.officeGroupIds,
      status: x.status
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('staffs.edit', staff),
      errors,
      progress,
      value: createFormValue(staff.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = staff.value!.id
          await staffStore.update({ form, id })
          await catchErrorStack(() => $router.replace(`/staffs/${id}`))
          $snackbar.success('スタッフの基本情報を編集しました。')
        }),
        (error: Error) => $alert.error('スタッフの基本情報の編集に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: 'スタッフ基本情報を編集'
  })
})
</script>
