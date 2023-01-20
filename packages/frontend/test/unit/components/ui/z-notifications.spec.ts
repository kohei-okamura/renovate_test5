/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { JobStatus } from '@zinger/enums/lib/job-status'
import ZNotifications from '~/components/ui/z-notifications.vue'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-notifications.vue', () => {
  const { mount } = setupComponentTest()

  it('should be rendered correctly', () => {
    const propsData = {
      items: [
        { id: 2, status: JobStatus.inProgress },
        { id: 1, status: JobStatus.failure }
      ]
    }
    const wrapper = mount(ZNotifications, { propsData })
    expect(wrapper).toMatchSnapshot()
  })

  it('Should not be rendered when it does not have notification', () => {
    const wrapper = mount(ZNotifications)
    expect(wrapper).toMatchSnapshot()
  })

  it('should emit "click" when button emitted "click"', async () => {
    const mockFn = jest.fn()
    const wrapper = mount({
      data: () => {
        return {
          items: [
            { id: 2, status: JobStatus.success },
            { id: 1, status: JobStatus.failure }
          ],
          handleClick: mockFn
        }
      },
      template: '<z-notifications :items="items" @click:delete="handleClick" />',
      components: { 'z-notifications': ZNotifications }
    })
    const nWrapper = wrapper.findAll('[data-notification]').at(0)
    await nWrapper.vm.$emit('click')
    expect(mockFn).toHaveBeenCalledTimes(1)
  })

  it('should emit "click:delete-all" when delete-all button clicked', async () => {
    const mockFn = jest.fn()
    const wrapper = mount({
      data: () => {
        return {
          items: [{ id: 1, status: JobStatus.failure }],
          handleClick: mockFn
        }
      },
      template: '<z-notifications :items="items" @click:delete-all="handleClick" />',
      components: { 'z-notifications': ZNotifications }
    })
    const buttonWrapper = wrapper.find('[data-delete-all-button]')
    await buttonWrapper.trigger('click')
    expect(mockFn).toHaveBeenCalledTimes(1)
  })
})
