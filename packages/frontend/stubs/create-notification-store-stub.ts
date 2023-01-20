/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive } from '@nuxtjs/composition-api'
import { noop } from '@zinger/helpers'
import * as m from '~/composables/stores/use-notification-store'
import { createStore } from '~/composables/stores/utils'

type Data = m.NotificationData
type Store = m.NotificationStore

export const createNotificationStoreStub = (data: Partial<Data> = {}): Store => {
  const state = reactive({
    ...m.createNotificationState(),
    ...data
  })
  const getters = {
    hasNotification: computed(() => state.notifications.length >= 1)
  }
  const actions = {
    addNotification: noop,
    updateNotification: noop,
    removeNotification: noop,
    removeCompletionNotifications: noop,
    updateIsDisplayed: noop,
    toggleIsDisplayed: noop,
    resetState: noop
  }
  const store: Store = createStore({ actions, getters, state })
  jest.spyOn(m, 'useNotificationStore').mockReturnValue(store)
  return store
}
