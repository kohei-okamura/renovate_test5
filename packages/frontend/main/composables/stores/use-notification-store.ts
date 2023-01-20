/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, InjectionKey, reactive } from '@nuxtjs/composition-api'
import { JobStatus } from '@zinger/enums/lib/job-status'
import { assign } from '@zinger/helpers'
import { createStore } from '~/composables/stores/utils'

type NotificationStatus = JobStatus

export type ZNotification = {
  id: string | number
  status: NotificationStatus
  featureName?: string
  linkToOnFailure?: string
  linkToOnSuccess?: string
  text?: string
}

type UnrefState = {
  notifications: ZNotification[]
  isDisplayed: boolean
}

export const createNotificationState = (): UnrefState => ({
  notifications: [],
  isDisplayed: false
})

export function useNotificationStore () {
  const state = reactive(createNotificationState())
  const getters = {
    hasNotification: computed(() => state.notifications.length >= 1)
  }
  const actions = {
    addNotification (notification: ZNotification) {
      state.notifications.unshift(notification)
    },
    updateNotification (notification: ZNotification) {
      const current = state.notifications.find(v => v.id === notification.id)
      if (current) {
        const updated = assign(current, notification)
        const notifications = state.notifications.map(v => v.id === notification.id ? updated : v)
        assign(state, { notifications })
      }
    },
    removeNotification (id: ZNotification['id']) {
      const notifications = state.notifications.filter(v => v.id !== id)
      assign(state, { notifications })
    },
    removeCompletionNotifications () {
      const notifications = state.notifications.filter(v => v.status === JobStatus.inProgress)
      assign(state, { notifications })
    },
    updateIsDisplayed (isDisplayed: UnrefState['isDisplayed']) {
      state.isDisplayed = isDisplayed
    },
    toggleIsDisplayed () {
      state.isDisplayed = !state.isDisplayed
    },
    resetState () {
      assign(state, createNotificationState())
    }
  }
  return createStore({ state, getters, actions })
}

export type NotificationData = ReturnType<typeof createNotificationState>

export type NotificationStore = ReturnType<typeof useNotificationStore>

export type NotificationState = NotificationStore['state']

export const notificationStoreKey: InjectionKey<NotificationStore> = Symbol('notificationStore')

export const notificationStateKey: InjectionKey<NotificationState> = Symbol('notificationState')
