<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-page compact :breadcrumbs="breadcrumbs">
    <v-alert text type="info">「登録情報」より更新を行えない項目は、管理者までお問い合わせください。</v-alert>
    <z-staff-edit-form
      button-text="保存"
      is-input-limited
      :errors="errors"
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
import { staffStateKey, staffStoreKey } from '~/composables/stores/use-staff-store'
import { useAxios } from '~/composables/use-axios'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { Staff } from '~/models/staff'
import { StaffsApi } from '~/services/api/staffs-api'

type Form = Partial<StaffsApi.UpdateForm>

export default defineComponent({
  name: 'SettingsProfileEditPage',
  middleware: [auth(Permission.updateStaffs)],
  setup () {
    const { $alert, $form, $router, $snackbar } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const staffStore = useInjected(staffStoreKey)
    const { staff } = useInjected(staffStateKey)
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
      status: x.status
    })
    $form.preventUnexpectedUnload()
    return {
      ...useBreadcrumbs('settings.profile.edit'),
      errors,
      progress,
      value: createFormValue(staff.value!),
      submit: (form: Form) => withAxios(
        () => $form.submit(async () => {
          const id = staff.value!.id
          await staffStore.update({ form, id })
          await catchErrorStack(() => $router.replace('/profile'))
          $snackbar.success('登録情報を更新しました。')
        }),
        (error: Error) => $alert.error('登録情報の更新に失敗しました。', error.stack)
      )
    }
  },
  head: () => ({
    title: 'スタッフ登録情報を編集'
  })
})
</script>
