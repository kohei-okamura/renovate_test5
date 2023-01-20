/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeGroupId } from '~/models/office-group'
import { VSelectOption } from '~/models/vuetify'
import { RefOrValues, unref } from '~/support/reactive'

type Params = RefOrValues<{
  permission: Permission
}>

const createOfficeGroupResolver = (xs: VSelectOption<OfficeGroupId>[]) => {
  return (id: OfficeGroupId | undefined, alternative = '-') => (id && xs.find(x => x.value === id)?.text) ?? alternative
}

export const useOfficeGroups = (params: Params) => {
  const { $api } = usePlugins()
  const state = reactive({
    isLoadingOfficeGroups: true,
    officeGroupOptions: [] as VSelectOption<OfficeGroupId>[]
  })
  const resolveOfficeGroupName = computed(() => createOfficeGroupResolver(state.officeGroupOptions))
  watch(
    computed(() => ({
      permission: unref(params.permission)
    })),
    async ({ permission }) => {
      state.isLoadingOfficeGroups = true
      state.officeGroupOptions = await $api.options.officeGroups({ permission })
      state.isLoadingOfficeGroups = false
    },
    { immediate: true }
  )
  return {
    ...toRefs(state),
    resolveOfficeGroupName
  }
}
