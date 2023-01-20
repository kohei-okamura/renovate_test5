/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeId } from '~/models/office'
import { StaffId } from '~/models/staff'
import { VSelectOption } from '~/models/vuetify'
import { RefOrValues, unref } from '~/support/reactive'

type Params = RefOrValues<{
  officeIds?: OfficeId[]
  permission: Permission
}>

const createStaffResolver = (xs: VSelectOption<StaffId>[]) => (id: StaffId | undefined, alternative = '-') => {
  return (id && xs.find(x => x.value === id)?.text) ?? alternative
}

export const useStaffs = (params: Params) => {
  const { $api } = usePlugins()
  const state = reactive({
    isLoadingStaffs: true,
    staffOptions: [] as VSelectOption<StaffId>[]
  })
  const resolveStaffName = computed(() => createStaffResolver(state.staffOptions))
  watch(
    computed(() => ({
      officeIds: unref(params.officeIds),
      permission: unref(params.permission)
    })),
    async ({ officeIds, permission }) => {
      state.isLoadingStaffs = true
      state.staffOptions = await $api.options.staffs({ permission, officeIds })
      state.isLoadingStaffs = false
    },
    { immediate: true }
  )
  return {
    ...toRefs(state),
    resolveStaffName
  }
}
