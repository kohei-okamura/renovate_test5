/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import ZNotification from '~/components/ui/z-notification.vue'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-notification.vue', () => {
  const { mount } = setupComponentTest()

  it.each([
    ['waiting', JobStatus.waiting],
    ['inProgress', JobStatus.inProgress],
    ['success', JobStatus.success],
    ['failure', JobStatus.failure]
  ])('should be rendered correctly when status is "%s"', (_, status) => {
    const propsData = {
      id: 1,
      status,
      linkToOnFailure: 'home',
      linkToOnSuccess: 'dashboard'
    }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered conditional elements correctly when status is "inProgress"', () => {
    const propsData = {
      id: 1,
      status: JobStatus.inProgress,
      linkToOnFailure: 'home',
      linkToOnSuccess: 'dashboard'
    }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper.find('[data-in-progress-icon]')).toExist()
    expect(wrapper.find('[data-success-icon]')).not.toExist()
    expect(wrapper.find('[data-failure-icon]')).not.toExist()
    expect(wrapper.find('[data-close-button]')).not.toExist()
    expect(wrapper.find('[data-detail-button-container-on-success]')).not.toExist()
    expect(wrapper.find('[data-detail-button-container-on-failure]')).not.toExist()
  })

  it('should be rendered conditional elements correctly when status is "success" (does not have linkToOnSuccess)', () => {
    const propsData = {
      id: 1,
      status: JobStatus.success,
      linkToOnFailure: 'home'
    }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper.find('[data-in-progress-icon]')).not.toExist()
    expect(wrapper.find('[data-success-icon]')).toExist()
    expect(wrapper.find('[data-failure-icon]')).not.toExist()
    expect(wrapper.find('[data-close-button]')).toExist()
    expect(wrapper.find('[data-detail-button-container-on-success]')).not.toExist()
    expect(wrapper.find('[data-detail-button-container-on-failure]')).not.toExist()
  })

  it('should be rendered conditional elements correctly when status is "success" (has linkToOnSuccess)', () => {
    const propsData = {
      id: 1,
      status: JobStatus.success,
      linkToOnFailure: 'home',
      linkToOnSuccess: 'dashboard'
    }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper.find('[data-in-progress-icon]')).not.toExist()
    expect(wrapper.find('[data-success-icon]')).toExist()
    expect(wrapper.find('[data-failure-icon]')).not.toExist()
    expect(wrapper.find('[data-close-button]')).toExist()
    expect(wrapper.find('[data-detail-button-container-on-success]')).toExist()
    expect(wrapper.find('[data-detail-button-container-on-failure]')).not.toExist()
  })

  it('should be rendered conditional elements correctly when status is "failure" (does not have linkToOnFailure)', () => {
    const propsData = {
      id: 1,
      status: JobStatus.failure,
      linkToOnSuccess: 'dashboard'
    }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper.find('[data-in-progress-icon]')).not.toExist()
    expect(wrapper.find('[data-success-icon]')).not.toExist()
    expect(wrapper.find('[data-failure-icon]')).toExist()
    expect(wrapper.find('[data-close-button]')).toExist()
    expect(wrapper.find('[data-detail-button-container-on-success]')).not.toExist()
    expect(wrapper.find('[data-detail-button-container-on-failure]')).not.toExist()
  })

  it('should be rendered conditional elements correctly when status is "failure" (has linkToOnFailure)', () => {
    const propsData = {
      id: 1,
      status: JobStatus.failure,
      linkToOnFailure: 'home',
      linkToOnSuccess: 'dashboard'
    }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper.find('[data-in-progress-icon]')).not.toExist()
    expect(wrapper.find('[data-success-icon]')).not.toExist()
    expect(wrapper.find('[data-failure-icon]')).toExist()
    expect(wrapper.find('[data-close-button]')).toExist()
    expect(wrapper.find('[data-detail-button-container-on-success]')).not.toExist()
    expect(wrapper.find('[data-detail-button-container-on-failure]')).toExist()
  })

  it.each([
    ['inProgress', JobStatus.inProgress],
    ['success', JobStatus.success],
    ['failure', JobStatus.failure]
  ])('should be rendered featureName if props.featureName is passed [status is "%s"]', (_, status) => {
    const featureName = 'My Awesome Feature'
    const propsData = { id: 1, status, featureName }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper.text()).toContain(featureName)
  })

  it.each([
    ['inProgress', [JobStatus.inProgress, 'in progress']],
    ['success', [JobStatus.success, 'terminating successfully']],
    ['failure', [JobStatus.failure, 'failing and ending']]
  ])('should be rendered text if props.text is passed [status is "%s"]', (_, [status, str]) => {
    const text = `The process is currently ${str}!`
    const propsData = { id: 1, status, text }
    const wrapper = mount(ZNotification, { propsData })
    expect(wrapper.text()).toContain(text)
  })

  it('should emit "click" with id when close button clicked', async () => {
    const mockFn = jest.fn()
    const id = 12345
    const wrapper = mount({
      data: () => {
        return {
          id,
          status: JobStatus.success,
          handleClick: mockFn
        }
      },
      template: '<z-notification :id="id" :status="status" @click="handleClick" />',
      components: { 'z-notification': ZNotification }
    })
    const buttonWrapper = wrapper.find('[data-close-button]')
    await buttonWrapper.trigger('click')
    expect(mockFn).toHaveBeenCalledTimes(1)
    expect(mockFn).toHaveBeenCalledWith(id)
  })

  it('should emit "click" with id when detail button clicked on failure', async () => {
    const $router = createMockedRouter()
    const spy = jest.spyOn($router, 'push')
    const mockFn = jest.fn()
    const id = 54321
    const linkToOnFailure = 'home'
    const wrapper = mount({
      data: () => {
        return {
          id,
          status: JobStatus.failure,
          linkToOnFailure,
          handleClick: mockFn
        }
      },
      template: '<z-notification :id="id" :status="status" :link-to-on-failure="linkToOnFailure" @click="handleClick" />',
      components: { 'z-notification': ZNotification }
    }, { mocks: { $router } })
    const buttonWrapper = wrapper.find('[data-detail-button-on-failure]')
    await buttonWrapper.trigger('click')
    expect(mockFn).toHaveBeenCalledTimes(1)
    expect(mockFn).toHaveBeenCalledWith(id)
    expect(spy).toHaveBeenCalledTimes(1)
    expect(spy).toHaveBeenCalledWith(linkToOnFailure)
    spy.mockRestore()
  })

  it('should emit "click" with id when detail button clicked on success', async () => {
    const $router = createMockedRouter()
    const spy = jest.spyOn($router, 'push')
    const mockFn = jest.fn()
    const id = 54321
    const linkToOnSuccess = 'home'
    const wrapper = mount({
      data: () => {
        return {
          id,
          status: JobStatus.success,
          linkToOnSuccess,
          handleClick: mockFn
        }
      },
      template: '<z-notification :id="id" :status="status" :link-to-on-success="linkToOnSuccess" @click="handleClick" />',
      components: { 'z-notification': ZNotification }
    }, { mocks: { $router } })
    const buttonWrapper = wrapper.find('[data-detail-button-on-success]')
    await buttonWrapper.trigger('click')
    expect(mockFn).toHaveBeenCalledTimes(1)
    expect(mockFn).toHaveBeenCalledWith(id)
    expect(spy).toHaveBeenCalledTimes(1)
    expect(spy).toHaveBeenCalledWith(linkToOnSuccess)
    spy.mockRestore()
  })
})
