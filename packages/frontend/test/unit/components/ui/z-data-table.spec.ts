/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import ZDataTable from '~/components/ui/z-data-table.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { Auth } from '~/models/auth'
import { ZDataTableOptions } from '~/models/z-data-table-options'
import { $icons } from '~/plugins/icons'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createMockedRouter } from '~~/test/helpers/create-mocked-router'
import { provides } from '~~/test/helpers/provides'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

const dummy = {
  headers: [
    { text: 'Dessert (100g serving)', value: 'name' },
    { text: 'Calories', value: 'calories' },
    { text: 'Fat (g)', value: 'fat' }
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

describe('z-data-table.vue', () => {
  const { mount } = setupComponentTest()
  const $router = createMockedRouter()
  const baseOptions: ZDataTableOptions<any> = {
    content: 'content',
    headers: dummy.headers
  }
  const baseProps = {
    items: dummy.items,
    options: baseOptions
  }
  let wrapper: Wrapper<Vue & any>

  const spTest = async (wrapper: Wrapper<Vue>, fb: (wrapper: Wrapper<Vue>) => void) => {
    await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.xs - 1 }, () => {
      fb(wrapper)
    })
  }

  function mountComponent (
    propsData = baseProps,
    auth: Partial<Auth> = { isSystemAdmin: true }
  ) {
    wrapper = mount(ZDataTable, {
      ...provides(
        [sessionStoreKey, createAuthStub(auth)]
      ),
      propsData,
      mocks: { $router }
    })
  }

  afterEach(() => {
    wrapper.destroy()
    jest.clearAllMocks()
  })

  it('pc layout should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
  })

  it('fab(floating action button) should be rendered by pc layout', () => {
    const propsData = {
      ...baseProps,
      ...{
        options: {
          ...baseOptions,
          fab: {
            icon: $icons.add,
            to: 'new'
          }
        }
      }
    }
    mountComponent(propsData)
    const fab = wrapper.find('a[class*="z-fab"]')
    expect(fab.exists()).toBeTrue()
  })

  it('fab(floating action button) should not be rendered by pc layout', () => {
    mountComponent()
    const fab = wrapper.find('a[class*="z-fab"]')
    expect(fab.exists()).toBeFalse()
  })

  it('loading message should be rendered when loading is true', () => {
    const propsData = {
      ...baseProps,
      ...{
        items: [],
        loading: true,
        options: {
          ...baseOptions,
          ...{
            headers: []
          }
        }
      }
    }
    const text = '読み込み中...'
    mountComponent(propsData)
    const element = wrapper.find('div[data-desktop-table]')
    expect(element.text()).toContain(text)
  })

  it('text when there is no data should be rendered', () => {
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
    const text = `該当する${baseOptions.content}は登録されていません。`
    mountComponent(propsData)
    const element = wrapper.find('div[data-desktop-table]')
    expect(element.text()).toContain(text)
  })

  it('should call $router.push when a row in the table is clicked, if props.itemLink is passed', () => {
    jest.spyOn($router, 'push')
    const propsData = {
      ...baseProps,
      ...{
        options: {
          ...baseOptions,
          ...{ itemLink: () => 'link to' }
        }
      }
    }
    mountComponent(propsData)
    wrapper.find('div[data-desktop-table]').vm.$emit('click:row')
    expect($router.push).toHaveBeenCalled()
  })

  it('should not call $router.push when a row in the table is clicked, if props.itemLink is not passed', () => {
    jest.spyOn($router, 'push')
    mountComponent()
    wrapper.find('div[data-desktop-table]').vm.$emit('click:row')
    expect($router.push).not.toHaveBeenCalled()
  })

  it('should emit click:row when a row in the table is clicked, if props.clickable is true', () => {
    jest.spyOn($router, 'push')
    const propsData = {
      ...baseProps,
      clickable: true
    }
    mountComponent(propsData)
    wrapper.find('div[data-desktop-table]').vm.$emit('click:row')
    const emitted = wrapper.emitted('click:row')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toHaveLength(1)
  })

  it('should emit click:row when a row in the table is clicked, if props.clickable is false (default)', () => {
    jest.spyOn($router, 'push')
    mountComponent()
    wrapper.find('div[data-desktop-table]').vm.$emit('click:row')
    expect(wrapper.emitted('click:row')).toBeUndefined()
  })

  it('smart phone layout should be rendered correctly', async () => {
    mountComponent()
    await spTest(wrapper, wrapper => {
      expect(wrapper).toMatchSnapshot()
    })
  })

  it('fab(floating action button) should be rendered by smart phone layout', async () => {
    const propsData = {
      ...baseProps,
      ...{
        options: {
          ...baseOptions,
          fab: {
            icon: $icons.add,
            to: 'new'
          }
        }
      }
    }
    mountComponent(propsData)
    await spTest(wrapper, wrapper => {
      const fab = wrapper.find('a[class*="z-fab"]')
      expect(fab.exists()).toBeTrue()
    })
  })

  it('fab(floating action button) should not be rendered by smart phone layout', async () => {
    mountComponent()
    await spTest(wrapper, wrapper => {
      const fab = wrapper.find('a[class*="z-fab"]')
      expect(fab.exists()).toBeFalse()
    })
  })

  it('item link should not be rendered', async () => {
    mountComponent()
    await spTest(wrapper, wrapper => {
      const link = wrapper.find('*[class*="z-data-table-mobile-row__actions"]')
      expect(link.exists()).toBeFalse()
    })
  })

  it('item link should be rendered with default text', async () => {
    const propsData = {
      ...baseProps,
      ...{
        options: {
          ...baseOptions,
          ...{
            itemLink: () => 'link to'
          }
        }
      }
    }
    mountComponent(propsData)
    await spTest(wrapper, wrapper => {
      const link = wrapper.find('*[data-mobile-actions]')
      expect(link.text()).toContain('詳細を見る')
    })
  })

  it('item link should be rendered with specified text', async () => {
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
    await spTest(wrapper, wrapper => {
      const link = wrapper.find('*[data-mobile-actions]')
      expect(link.text()).toContain(itemLinkText)
    })
  })

  describe('isItemLinkEnabled', () => {
    const requiredPermissions: Permission[] = [
      Permission.viewShifts
    ]
    const propsData = {
      ...baseProps,
      ...{
        options: {
          ...baseOptions,
          ...{
            itemLink: () => 'link to',
            itemLinkPermissions: [Permission.viewShifts]
          }
        }
      }
    }

    it('should be true when session auth is system admin', () => {
      mountComponent(propsData)
      expect(wrapper.vm.isItemLinkEnabled).toBeTrue()
    })

    it('should be false when props do not have item link', () => {
      mountComponent()
      expect(wrapper.vm.isItemLinkEnabled).toBeFalse()
    })

    it(`should be true when the staff has permission: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent(propsData, { permissions })
      expect(wrapper.vm.isItemLinkEnabled).toBeTrue()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent(propsData, { permissions })
      expect(wrapper.vm.isItemLinkEnabled).toBeFalse()
    })
  })

  describe('isFooterLinkEnabled', () => {
    const requiredPermissions: Permission[] = [Permission.createDwsContracts]
    const propsData = {
      ...baseProps,
      ...{
        options: {
          ...baseOptions,
          ...{
            footerLink: 'link to',
            footerLinkPermissions: [Permission.createDwsContracts],
            footerLinkText: '契約を登録'
          }
        }
      }
    }

    it('should be true when session auth is system admin', () => {
      mountComponent(propsData)
      expect(wrapper.vm.isFooterLinkEnabled).toBeTrue()
    })

    it('should be false when props do not have footerLink and footerLinkPermissions', () => {
      mountComponent()
      expect(wrapper.vm.isFooterLinkEnabled).toBeFalse()
    })

    it(`should be true when the staff has permission: ${requiredPermissions}`, () => {
      const permissions = requiredPermissions
      mountComponent(propsData, { permissions })
      expect(wrapper.vm.isFooterLinkEnabled).toBeTrue()
    })

    it(`should not be rendered when the staff does not have permissions: ${requiredPermissions}`, () => {
      const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
      mountComponent(propsData, { permissions })
      expect(wrapper.vm.isFooterLinkEnabled).toBeFalse()
    })
  })
})
