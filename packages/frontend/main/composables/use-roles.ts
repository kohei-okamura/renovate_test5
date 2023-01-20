/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs, watch } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import { usePlugins } from '~/composables/use-plugins'
import { RoleId } from '~/models/role'
import { VSelectOption } from '~/models/vuetify'
import { RefOrValue, unref } from '~/support/reactive'

type Params = {
  permission: RefOrValue<Permission>
}

const createRoleResolver = (xs: VSelectOption<RoleId>[]) => {
  return (id: RoleId | undefined, alternative = '-') => (id && xs.find(x => x.value === id)?.text) ?? alternative
}

export const useRoles = (params: Params) => {
  const { $api } = usePlugins()
  const state = reactive({
    isLoadingRoles: true,
    roleOptions: [] as VSelectOption<RoleId>[]
  })
  const resolveRoleName = computed(() => createRoleResolver(state.roleOptions))
  watch(
    () => unref(params.permission),
    async permission => {
      state.isLoadingRoles = true
      state.roleOptions = await $api.options.roles({ permission })
      state.isLoadingRoles = false
    },
    { immediate: true }
  )
  return {
    ...toRefs(state),
    resolveRoleName
  }
}
