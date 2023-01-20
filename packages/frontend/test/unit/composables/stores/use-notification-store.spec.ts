/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import { keys } from '@zinger/helpers'
import { NotificationState, NotificationStore, useNotificationStore } from '~/composables/stores/use-notification-store'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/stores/use-notification-store', () => {
  let store: NotificationStore
  let state: NotificationState

  beforeAll(() => {
    setupComposableTest()
    store = useNotificationStore()
    state = store.state
  })

  describe('state', () => {
    afterAll(() => {
      store.resetState()
    })

    it('should have 3 values (2 states, 1 getters)', () => {
      expect(keys(state)).toHaveLength(3)
    })

    describe('notifications', () => {
      it('should be ref to empty array', () => {
        expect(state.notifications).toBeRef()
        expect(state.notifications.value).toBeEmptyArray()
      })
    })

    describe('isDisplayed', () => {
      it('should be ref to false', () => {
        expect(state.isDisplayed).toBeRef()
        expect(state.isDisplayed.value).toBeFalse()
      })
    })

    describe('hasNotification', () => {
      it('should be ref to false', () => {
        expect(state.hasNotification).toBeRef()
        expect(state.hasNotification.value).toBeFalse()
      })
    })
  })

  describe('actions', () => {
    afterEach(() => {
      store.resetState()
    })

    it('addNotification', async () => {
      expect(state.notifications.value).toBeEmptyArray()

      const notification = { id: 1, status: JobStatus.inProgress }

      await store.addNotification(notification)

      expect(state.notifications.value).toHaveLength(1)
      expect(state.notifications.value[0]).toStrictEqual(notification)
    })

    it('updateNotification', async () => {
      const n1 = { id: 1, status: JobStatus.inProgress }
      const n2 = { id: 2, status: JobStatus.failure }

      await store.addNotification(n1)
      await store.addNotification(n2)

      expect(state.notifications.value).toHaveLength(2)
      expect(state.notifications.value[0]).toStrictEqual(n2)
      expect(state.notifications.value[1]).toStrictEqual(n1)

      const updated = { id: 1, status: JobStatus.success, text: 'The process is complete.' }

      await store.updateNotification(updated)

      expect(state.notifications.value).toHaveLength(2)
      expect(state.notifications.value[0]).toStrictEqual(n2)
      expect(state.notifications.value[1]).toStrictEqual(updated)
    })

    it('removeNotification', async () => {
      const n1 = { id: 1, status: JobStatus.failure }
      const n2 = { id: 2, status: JobStatus.success }

      await store.addNotification(n1)
      await store.addNotification(n2)

      expect(state.notifications.value).toHaveLength(2)
      expect(state.notifications.value[0]).toStrictEqual(n2)

      await store.removeNotification(n2.id)

      expect(state.notifications.value).toHaveLength(1)
      expect(state.notifications.value[0]).toStrictEqual(n1)
    })

    it('removeCompletionNotifications (all processes are complete)', async () => {
      const notifications = [
        { id: 1, status: JobStatus.failure },
        { id: 2, status: JobStatus.failure },
        { id: 3, status: JobStatus.failure },
        { id: 4, status: JobStatus.success }
      ]

      await Promise.all(notifications.map(async v => await store.addNotification(v)))

      expect(state.notifications.value).toHaveLength(4)

      await store.removeCompletionNotifications()

      expect(state.notifications.value).toHaveLength(0)
    })

    it('removeCompletionNotifications (one processes is in progress)', async () => {
      const notifications = [
        { id: 1, status: JobStatus.failure },
        { id: 2, status: JobStatus.failure },
        { id: 3, status: JobStatus.inProgress },
        { id: 4, status: JobStatus.success }
      ]

      await Promise.all(notifications.map(async v => await store.addNotification(v)))

      expect(state.notifications.value).toHaveLength(4)

      await store.removeCompletionNotifications()

      expect(state.notifications.value).toHaveLength(1)
      expect(state.notifications.value[0]).toStrictEqual(notifications[2])
    })

    it('updateIsDisplayed', async () => {
      expect(state.isDisplayed.value).toBeFalse()

      await store.updateIsDisplayed(true)

      expect(state.isDisplayed.value).toBeTrue()
    })

    it('toggleIsDisplayed', async () => {
      expect(state.isDisplayed.value).toBeFalse()

      await store.toggleIsDisplayed()

      expect(state.isDisplayed.value).toBeTrue()

      await store.toggleIsDisplayed()

      expect(state.isDisplayed.value).toBeFalse()
    })

    it('resetState', async () => {
      const initialState = useNotificationStore().state

      await store.addNotification({ id: 1, status: JobStatus.success })
      await store.updateIsDisplayed(true)

      expect(state).not.toMatchObject(initialState)

      await store.resetState()

      expect(state).toMatchObject(initialState)
    })
  })
})
