/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs } from '@nuxtjs/composition-api'
import { useStaffs } from '~/composables/use-staffs'
import { Staff, StaffId } from '~/models/staff'
import { createStaffStubs } from '~~/stubs/create-staff-stub'

export const createUseStaffsStub = (staffs?: Staff[]): ReturnType<typeof useStaffs> => {
  const options = (staffs ?? createStaffStubs()).map(x => ({ value: x.id, text: x.name.displayName }))
  const resolveStaffName = computed(() => (id: StaffId | undefined, alternative = '-') => {
    return (id && options.find(x => x.value === id)?.text) ?? alternative
  })
  const data = reactive({
    isLoadingStaffs: false,
    staffOptions: options
  })
  return {
    ...toRefs(data),
    resolveStaffName
  }
}
