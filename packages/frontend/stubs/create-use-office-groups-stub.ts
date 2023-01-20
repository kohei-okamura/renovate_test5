/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs } from '@nuxtjs/composition-api'
import { useOfficeGroups } from '~/composables/use-office-groups'
import { OfficeGroup, OfficeGroupId } from '~/models/office-group'
import { createOfficeGroupStubs } from '~~/stubs/create-office-group-stub'

export const createUseOfficeGroupsStub = (officeGroups?: OfficeGroup[]): ReturnType<typeof useOfficeGroups> => {
  const options = (officeGroups ?? createOfficeGroupStubs()).map(x => ({ value: x.id, text: x.name }))
  const resolveOfficeGroupName = computed(() => (id: OfficeGroupId | undefined, alternative = '-') => {
    return (id && options.find(x => x.value === id)?.text) ?? alternative
  })
  const data = reactive({
    isLoadingOfficeGroups: false,
    officeGroupOptions: options
  })
  return {
    ...toRefs(data),
    resolveOfficeGroupName
  }
}
