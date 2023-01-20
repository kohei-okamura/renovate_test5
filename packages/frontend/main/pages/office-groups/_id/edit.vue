<!--
  - Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
  - UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
  -->
<template>
  <z-office-group-form
    v-if="isResolved"
    button-text="保存"
    title="事業所グループを編集"
    :dialog="dialog"
    :errors="errors"
    :progress="progress"
    :value="resolvedValue"
    @submit="submit"
    @update:dialog="toggleDialog"
  />
</template>

<script lang="ts">
import { defineComponent } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { catchErrorStack } from '~/composables/catch-error-stack'
import { officeGroupsStoreKey } from '~/composables/stores/use-office-groups-store'
import { useAsync } from '~/composables/use-async'
import { useAxios } from '~/composables/use-axios'
import { useDialogBindings } from '~/composables/use-dialog-bindings'
import { useInjected } from '~/composables/use-injected'
import { usePlugins } from '~/composables/use-plugins'
import { auth } from '~/middleware/auth'
import { NuxtContext } from '~/models/nuxt'
import { OfficeGroupsApi } from '~/services/api/office-groups-api'

export default defineComponent({
  name: 'OfficeGroupsEditPage',
  middleware: [auth(Permission.updateOfficeGroups)],
  validate ({ params }: NuxtContext) {
    return /^[1-9]\d*$/.test(params.id)
  },
  setup () {
    const { $alert, $api, $snackbar, $route, $router } = usePlugins()
    const { errors, progress, withAxios } = useAxios()
    const { dialog, toggleDialog } = useDialogBindings()
    const officeGroupsStore = useInjected(officeGroupsStoreKey)
    const id = +$route.params.id
    const { isResolved, resolvedValue } = useAsync(async () => {
      const { officeGroup } = await $api.officeGroups.get({ id })
      return {
        name: officeGroup.name,
        parentOfficeGroupId: officeGroup.parentOfficeGroupId,
        sortOrder: officeGroup.sortOrder
      }
    })
    const submit = (form: OfficeGroupsApi.Form) => withAxios(
      async () => {
        await officeGroupsStore.update({ form, id })
        await catchErrorStack(() => $router.replace('/office-groups'))
        $snackbar.success('事業所グループを編集しました。')
      },
      (error: Error) => $alert.error('事業所グループの編集に失敗しました', error.stack)
    )
    return {
      dialog,
      errors,
      isResolved,
      progress,
      resolvedValue,
      toggleDialog,
      submit
    }
  },
  head: () => ({
    title: '事業所グループを編集'
  })
})
</script>
