<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-office-group-form
    button-text="登録"
    title="事業所グループを登録"
    :dialog="dialog"
    :errors="errors"
    :progress="progress"
    :value="value"
    @submit="submit"
    @update:dialog="toggleDialog"
  />
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { officeGroupsStoreKey } from '~/composables/stores/use-office-groups-store'
import { useAxios } from '~/composables/use-axios'
import { useDialogBindings } from '~/composables/use-dialog-bindings'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'

type Form = Partial<OfficeGroupsApi.Form>

export default defineComponent({
  name: 'OfficeGroupsNewPage',
  middleware: [auth(Permission.createOfficeGroups)],
  setup () {
    const { $alert, $api, $snackbar, $route, $router } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { dialog, toggleDialog } = useDialogBindings()
    const officeGroupsStore = useInjected(officeGroupsStoreKey)
    const query = $route.query
    const value = {
      name: '',
      parentOfficeGroupId: query.parentOfficeGroupId ? +query.parentOfficeGroupId : undefined
    }
    const submit = (form: Form) => withAxios(
      async () => {
        await $api.officeGroups.create({ form })
        await catchErrorStack(async () => {
          await officeGroupsStore.getIndex()
          await $router.replace('/office-groups')
        })
        $snackbar.success('事業所グループを登録しました。')
      },
      (error: Error) => $alert.error('事業所グループの登録に失敗しました。', error.stack)
    )
    return {
      dialog,
      errors,
      progress,
      value,
      submit,
      toggleDialog
    }
  },
  head: () => ({
    title: '事業所グループを登録'
  })
})
</script>
