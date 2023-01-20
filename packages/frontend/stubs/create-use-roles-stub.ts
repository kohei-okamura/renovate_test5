/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs } from '@nuxtjs/composition-api'
import { useRoles } from '~/composables/use-roles'
import { Role, RoleId } from '~/models/role'
import { createRoleStubs } from '~~/stubs/create-role-stub'

export const createUseRolesStub = (roles?: Role[]): ReturnType<typeof useRoles> => {
  const options = (roles ?? createRoleStubs()).map(x => ({ value: x.id, text: x.name }))
  const resolveRoleName = computed(() => (id: RoleId | undefined, alternative = '-') => {
    return (id && options.find(x => x.value === id)?.text) ?? alternative
  })
  const data = reactive({
    isLoadingRoles: false,
    roleOptions: options
  })
  return {
    ...toRefs(data),
    resolveRoleName
  }
}
