/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import ZDataExpansionPanel from '~/components/ui/z-data-accordion.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { ZDataAccordionOptions } from '~/models/z-data-accordion-options'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { provides } from '~~/test/helpers/provides'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'
import { click } from '~~/test/helpers/trigger'

describe('z-data-accordion.vue', () => {
  const { mount } = setupComponentTest()
  const dummy = {
    headers: [
      { text: 'Dessert (100g serving)', value: 'name' },
      { text: 'Calories', value: 'calories' }
    ],
    items: [
      {
        name: 'Frozen Yogurt',
        calories: 159,
        fat: 6.0
      },
      {
        name: 'Ice cream sandwich',
        calories: 237,
        fat: 9.0
      }
    ]
  }
  const baseOptions: ZDataAccordionOptions<any> = {
    content: 'content',
    headers: dummy.headers
  }
  const baseProps = {
    items: dummy.items,
    options: baseOptions
  }
  let wrapper: Wrapper<Vue>

  function mountComponent (
    propsData = baseProps,
    auth: Partial<Auth> = { isSystemAdmin: true }
  ) {
    wrapper = mount(ZDataExpansionPanel, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth)]
      ),
      propsData
    })
  }

  afterEach(() => {
    wrapper.destroy()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
  })

  it('should be rendered with expanded contents when click expansion panel header', async () => {
    mountComponent()
    await click(() => wrapper.find('[data-expansion-panel-header]'))
    expect(wrapper).toMatchSnapshot()
  })

  it('should contain text when there is no data', () => {
    const propsData = {
      ...baseProps,
      ...{
        items: [],
        options: {
          ...baseOptions,
          ...{
            headers: []
          }
        }
      }
    }

    mountComponent(propsData)

    const text = wrapper.text()
    expect(text).toContain(`該当する${baseOptions.content}は登録されていません。`)
  })

  describe('item link', () => {
    const requiredPermissions: Permission[] = [Permission.updateInternalOffices]

    it('should not be rendered when not expanded', () => {
      mountComponent()
      expect(wrapper).not.toContainElement('[data-accordion-panel-link]')
    })

    it('should be rendered when expanded', async () => {
      const propsData = {
        ...baseProps,
        options: {
          ...baseOptions,
          itemLink: () => 'link to'
        }
      }

      mountComponent(propsData)
      await click(() => wrapper.find('[data-expansion-panel-header]'))

      expect(wrapper).toContainElement('[data-accordion-panel-link]')
    })

    it('should contain default text', async () => {
      const propsData = {
        ...baseProps,
        options: {
          ...baseOptions,
          itemLink: () => 'link to'
        }
      }

      mountComponent(propsData)
      await click(() => wrapper.find('[data-expansion-panel-header]'))

      const text = wrapper.find('[data-accordion-panel-link]').text()
      expect(text).toContain('詳細を見る')
    })

    it('should contain specified text', async () => {
      const itemLinkText = 'show more details'
      const propsData = {
        ...baseProps,
        ...{
          options: {
            ...baseOptions,
            ...{
              itemLink: () => 'link to',
              itemLinkText
            }
          }
        }
      }

      mountComponent(propsData)
      await click(() => wrapper.find('[data-expansion-panel-header]'))

      const text = wrapper.find('[data-accordion-panel-link]').text()
      expect(text).toContain(itemLinkText)
    })

    it(`should be rendered when there are both itemLink and permission: ${requiredPermissions}`, async () => {
      const permissions = requiredPermissions
      const propsData = {
        ...baseProps,
        options: {
          ...baseOptions,
          itemLink: () => 'link to',
          itemLinkPermissions: requiredPermissions
        }
      }
      mountComponent(propsData, { permissions })
      await click(() => wrapper.find('[data-expansion-panel-header]'))
      const text = wrapper.find('[data-accordion-panel-link]').text()
      expect(text).toContain('詳細を見る')
    })

    it(`should not be rendered when there is not permission: ${requiredPermissions}`, async () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      const propsData = {
        ...baseProps,
        options: {
          ...baseOptions,
          itemLink: () => 'link to',
          itemLinkPermissions: [Permission.updateInternalOffices]
        }
      }
      mountComponent(propsData, { permissions })
      await click(() => wrapper.find('[data-expansion-panel-header]'))
      expect(wrapper).not.toContain('data-accordion-panel-link')
    })
  })
})
