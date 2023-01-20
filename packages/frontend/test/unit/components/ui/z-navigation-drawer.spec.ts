/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Stubs, Wrapper } from '@vue/test-utils'
import { Permission } from '@zinger/enums/lib/permission'
import { createMock } from '@zinger/helpers/testing/create-mock'
import Vue from 'vue'
import ZNavigationDrawer from '~/components/ui/z-navigation-drawer.vue'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import * as UseMatchMedia from '~/composables/use-match-media'
import { Auth } from '~/models/auth'
import { Menu } from '~/models/menu'
import { Plugins } from '~/plugins'
import { createDrawerService, DrawerService } from '~/services/drawer-service'
import { createAuthStub } from '~~/test/helpers/create-auth-stub'
import { createComponentStub } from '~~/test/helpers/create-component-stub'
import { createMockedRoutes } from '~~/test/helpers/create-mocked-routes'
import { provides } from '~~/test/helpers/provides'
import { resizeWindow } from '~~/test/helpers/resize-window'
import { setupComponentTest } from '~~/test/helpers/setup-component-test'

describe('z-navigation-drawer.vue', () => {
  const { mount } = setupComponentTest()
  const $routes = createMockedRoutes({})
  const $drawer = createMock<DrawerService>({
    ...createDrawerService()
  })
  const mocks: Partial<Plugins> = {
    $drawer,
    $routes
  }
  const stubs: Stubs = {
    'v-list-group': createComponentStub('v-list-group', ['activator', 'default'])
  }
  let wrapper: Wrapper<Vue & any>

  const DRAWER_TRANSFORM_ACTIVE = 'translateX(0%)'
  const DRAWER_TRANSFORM_FIXED = 'translateX(0%)'
  const DRAWER_TRANSFORM_INACTIVE = 'translateX(-100%)'
  const DRAWER_TRANSFORM_NOT_FIXED = 'translateX(-100%)'

  type MountComponentOptions = {
    auth?: Partial<Auth>
    hasCoarsePointer?: boolean
  }

  function mountComponent ({ auth = { isSystemAdmin: true }, hasCoarsePointer = false }: MountComponentOptions = {}) {
    jest.spyOn(UseMatchMedia, 'useMatchMedia').mockReturnValue({
      hasCoarsePointer: () => hasCoarsePointer
    })
    wrapper = mount(ZNavigationDrawer, {
      mocks,
      stubs,
      ...provides([sessionStoreKey, createAuthStub(auth)])
    })
  }

  const getMenu = (): Menu.Element[] => wrapper.vm.menu
  const getTexts = (selector: string): string[] => wrapper.findAll(selector).wrappers.map(x => x.text())

  afterEach(() => {
    jest.restoreAllMocks()
  })

  it('should be rendered correctly', () => {
    mountComponent()
    expect(wrapper).toMatchSnapshot()
    wrapper.destroy()
  })

  describe('drawer on a wide screen', () => {
    it.each([
      ['should be fixed when the device does not have coarse pointer', false, DRAWER_TRANSFORM_FIXED],
      ['should not be fixed when the device has coarse pointer', true, DRAWER_TRANSFORM_NOT_FIXED]
    ])('%s', (_, hasCoarsePointer, transform) => {
      mountComponent({ hasCoarsePointer })
      expect(wrapper.find<HTMLElement>('[data-navigation-drawer]').element.style.transform).toBe(transform)
      wrapper.destroy()
    })
  })

  describe('drawer on a narrow screen', () => {
    it.each([
      ['should not be fixed when the device does not have coarse pointer', false, DRAWER_TRANSFORM_NOT_FIXED],
      ['should not be fixed when the device has coarse pointer', true, DRAWER_TRANSFORM_NOT_FIXED]
    ])('%s', async (_, hasCoarsePointer, transform) => {
      mountComponent({ hasCoarsePointer })
      const width = wrapper.vm.$vuetify.breakpoint.thresholds.sm - 1
      await resizeWindow({ width }, () => {
        expect(wrapper.find<HTMLElement>('[data-navigation-drawer]').element.style.transform).toBe(transform)
        wrapper.destroy()
      })
    })

    it.each([
      ['should be shown', true, DRAWER_TRANSFORM_ACTIVE],
      ['should not be shown', false, DRAWER_TRANSFORM_INACTIVE]
    ])('%s when $drawer.isOpened is %p', async (_, isOpened, transform) => {
      mountComponent()
      await resizeWindow({ width: wrapper.vm.$vuetify.breakpoint.thresholds.sm - 1 }, async () => {
        // `$drawer.set` は非同期関数ではないが `await` すると後続の処理が正常に動くので `await` する
        await $drawer.set(isOpened)
        expect(wrapper.find<HTMLElement>('[data-navigation-drawer]').element.style.transform).toBe(transform)
        wrapper.destroy()
      })
    })
  })

  describe('menu', () => {
    it('should be rendered with all items when the staff is system admin', () => {
      mountComponent()
      const menu = getMenu()
      const titles = menu.map(x => x.text)
      const children = menu.flatMap(x => 'children' in x ? x.children.map(x => x.text) : [])

      expect(getTexts('[data-menu-item-title]')).toEqual(titles)
      expect(getTexts('[data-menu-item-children-title]')).toEqual(children)

      wrapper.destroy()
    })

    describe('ダッシュボード', () => {
      it('should be rendered even if the staff does not have any permission', () => {
        mountComponent({ auth: {} })
        expect(getTexts('[data-menu-item-title]')).toEqual(['ダッシュボード'])
        wrapper.destroy()
      })
    })
    describe.each<[string, Permission[]]>([
      ['台帳管理', [Permission.listUsers, Permission.listStaffs, Permission.listInternalOffices, Permission.listExternalOffices, Permission.listOfficeGroups, Permission.listOwnExpensePrograms]],
      ['請求管理', [Permission.listBillings, Permission.listUserBillings]],
      ['設定', [Permission.listRoles, Permission.viewOrganizationSettings]]
    ])('%s', (title, requiredPermissions) => {
      it(`should be rendered when the staff has permission(s): ${requiredPermissions.join(', ')}`, () => {
        const permissions = requiredPermissions
        mountComponent({ auth: { permissions } })
        expect(getTexts('[data-menu-item-title]')).toContain(title)
      })

      it(`should not be rendered when the staff does not have permission(s): ${requiredPermissions.join(', ')}`, () => {
        const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
        mountComponent({ auth: { permissions } })
        expect(getTexts('[data-menu-item-title]')).not.toContain(title)
      })
    })

    describe.each<string, Permission[]>([
      ['利用者', [Permission.listUsers]],
      ['スタッフ', [Permission.listStaffs]],
      ['事業所', [Permission.listInternalOffices, Permission.listExternalOffices]],
      ['事業所グループ', [Permission.listOfficeGroups]],
      ['自費サービス', [Permission.listOwnExpensePrograms]],
      ['障害福祉サービス請求', [Permission.listBillings]],
      ['介護保険サービス請求', [Permission.listBillings]],
      ['ロール', [Permission.listRoles]]
    ])('%s', (title, requiredPermissions) => {
      it(`should be rendered when the staff has permission(s): ${requiredPermissions.join(', ')}`, () => {
        const permissions = requiredPermissions
        mountComponent({ auth: { permissions } })
        expect(getTexts('[data-menu-item-children-title]')).toContain(title)
      })

      it(`should not be rendered when the staff does not have permission(s): ${requiredPermissions.join(', ')}`, () => {
        const permissions = Permission.values.filter(x => !requiredPermissions.includes(x))
        mountComponent({ auth: { permissions } })
        expect(getTexts('[data-menu-item-children-title]')).not.toContain(title)
      })
    })
  })
})
