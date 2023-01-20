/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { usePlugins } from '~/composables/use-plugins'
import { OfficeId } from '~/models/office'
import { UserId } from '~/models/user'
import { VSelectOption } from '~/models/vuetify'
import { RefOrValues, unref } from '~/support/reactive'

type Params = RefOrValues<{
  officeIds?: OfficeId[]
  permission: Permission
}>

const createUserResolver = (xs: VSelectOption<UserId>[]) => (id: UserId | undefined, alternative = '-') => {
  return (id && xs.find(x => x.value === id)?.text) ?? alternative
}

export const useUsers = (params: Params) => {
  const { $api } = usePlugins()
  const state = reactive({
    isLoadingUsers: true,
    userOptions: [] as VSelectOption<UserId>[]
  })
  const resolveUserName = computed(() => createUserResolver(state.userOptions))
  watch(
    computed(() => ({
      officeIds: unref(params.officeIds),
      permission: unref(params.permission)
    })),
    async ({ officeIds, permission }) => {
      state.isLoadingUsers = true
      state.userOptions = await $api.options.users({ permission, officeIds })
      state.isLoadingUsers = false
    },
    { immediate: true }
  )
  return {
    ...toRefs(state),
    resolveUserName
  }
}
