/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { OfficeQualification } from '@zinger/enums/lib/office-qualification'
import { Permission } from '@zinger/enums/lib/permission'
import { Purpose } from '@zinger/enums/lib/purpose'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'
import { VSelectOption } from '~/models/vuetify'
import { RefOrValues, unref } from '~/support/reactive'

type BaseParams = {
  isCommunityGeneralSupportCenter?: boolean
  permission?: Permission
  qualifications?: OfficeQualification[]
  userId?: UserId
}
type ParamsWithPurpose = BaseParams & {
  internal?: never
  purpose?: Exclude<Purpose, typeof Purpose.unknown>
}
type ParamsWithInternal = BaseParams & {
  internal: true
  purpose?: never
}
type Params = RefOrValues<ParamsWithPurpose | ParamsWithInternal>

export type OfficeOption = VSelectOption<OfficeId> & {
  keyword: string
}

const createOfficeResolver = (xs: VSelectOption<OfficeId>[]) => {
  return (id: OfficeId | undefined, alternative = '-') => (id && xs.find(x => x.value === id)?.text) ?? alternative
}

export const useOffices = (params: Params) => {
  const { $api } = usePlugins()
  const state = reactive({
    isLoadingOffices: true,
    officeOptions: [] as OfficeOption[]
  })
  const resolveOfficeAbbr = computed(() => createOfficeResolver(state.officeOptions))
  watch(
    computed(() => ({
      isCommunityGeneralSupportCenter: unref(params.isCommunityGeneralSupportCenter),
      internal: unref(params.internal),
      permission: unref(params.permission),
      purpose: unref(params.purpose),
      qualifications: unref(params.qualifications),
      userId: unref(params.userId)
    })),
    async ({ internal, purpose, ...rest }) => {
      state.isLoadingOffices = true
      state.officeOptions = await $api.options.offices({ ...rest, purpose: internal ? Purpose.internal : purpose })
      state.isLoadingOffices = false
    },
    { immediate: true }
  )
  return {
    ...toRefs(state),
    resolveOfficeAbbr
  }
}
