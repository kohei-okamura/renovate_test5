/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Stubs, Wrapper } from '@vue/test-utils'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { notificationStoreKey } from '~/composables/stores/use-notification-store'
import DefaultLayout from '~/layouts/default.vue'
import { Plugins } from '~/plugins'
import { createSnackbarService, SnackbarService } from '~/services/snackbar-service'
import { createNotificationStoreStub } from '~~/stubs/create-notification-store-stub'
import { provides } from '~~/test/helpers/provides'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('layouts/default.vue', () => {
  const { shallowMount } = setupComponentTest()
  const notificationStore = createNotificationStoreStub({
    isDisplayed: true
  })
  const $snackbar = createMock<SnackbarService>({
    ...createSnackbarService()
  })
  const mocks: Partial<Plugins> = {
    $snackbar
  }
  const stubs: Stubs = {
    'z-confirm-dialog': true,
    'z-navigation-drawer': true
  }

  let wrapper: Wrapper<Vue>

  function mountComponent () {
    wrapper = shallowMount(DefaultLayout, {
      ...provides([notificationStoreKey, notificationStore]),
      mocks,
      stubs
    })
  }

  function unmountComponent () {
    wrapper.destroy()
  }

  it.each([
    ['greater than or equal', 0],
    ['less than', 1]
  ])('should be rendered correctly when %s a breakpoint', async (_, subtract) => {
    mountComponent()
    await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.xs - subtract }, () => {
      expect(wrapper).toMatchSnapshot()
    })
    unmountComponent()
  })

  describe('notification', () => {
    beforeAll(() => {
      mountComponent()
    })

    afterAll(() => {
      unmountComponent()
    })

    it('should call updateIsDisplayed when navigation icon clicked', async () => {
      await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.sm - 1 }, () => {
        jest.spyOn(notificationStore, 'updateIsDisplayed')

        wrapper.findComponent({ name: 'ZAppBar' }).vm.$emit('click:nav')

        expect(notificationStore.updateIsDisplayed).toHaveBeenCalledTimes(1)
        expect(notificationStore.updateIsDisplayed).toHaveBeenCalledWith(false)

        mocked(notificationStore.updateIsDisplayed).mockReset()
      })
    })

    it('should call toggleIsDisplayed when notification icon clicked', async () => {
      await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.sm - 1 }, () => {
        jest.spyOn(notificationStore, 'toggleIsDisplayed')

        wrapper.findComponent({ name: 'ZAppBar' }).vm.$emit('click:bell')

        expect(notificationStore.toggleIsDisplayed).toHaveBeenCalledTimes(1)

        mocked(notificationStore.toggleIsDisplayed).mockReset()
      })
    })

    it('should call removeNotification when notifications emitted "click:delete"', () => {
      const id = 100
      jest.spyOn(notificationStore, 'removeNotification')

      wrapper.findComponent({ name: 'ZNotifications' }).vm.$emit('click:delete', id)

      expect(notificationStore.removeNotification).toHaveBeenCalledTimes(1)
      expect(notificationStore.removeNotification).toHaveBeenCalledWith(id)

      mocked(notificationStore.removeNotification).mockReset()
    })

    it('should call removeCompletionNotifications when notifications emitted "click:delete-all"', () => {
      jest.spyOn(notificationStore, 'removeCompletionNotifications')

      wrapper.findComponent({ name: 'ZNotifications' }).vm.$emit('click:delete-all')

      expect(notificationStore.removeCompletionNotifications).toHaveBeenCalledTimes(1)

      mocked(notificationStore.removeCompletionNotifications).mockReset()
    })
  })
})
