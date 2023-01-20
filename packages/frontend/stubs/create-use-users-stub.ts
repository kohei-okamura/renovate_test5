/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs } from '@nuxtjs/composition-api'
import { useUsers } from '~/composables/use-users'
import { User, UserId } from '~/models/user'
import { createUserStubs } from '~~/stubs/create-user-stub'

export const createUseUsersStub = (users?: User[]): ReturnType<typeof useUsers> => {
  const options = (users ?? createUserStubs()).map(x => ({ value: x.id, text: x.name.displayName }))
  const resolveUserName = computed(() => (id: UserId | undefined, alternative = '-') => {
    return (id && options.find(x => x.value === id)?.text) ?? alternative
  })
  const data = reactive({
    isLoadingUsers: false,
    userOptions: options
  })
  return {
    ...toRefs(data),
    resolveUserName
  }
}
