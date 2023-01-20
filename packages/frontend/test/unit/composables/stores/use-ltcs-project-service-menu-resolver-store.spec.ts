/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import {
  LtcsProjectServiceMenuResolverStore,
  useLtcsProjectServiceMenuResolverStore
} from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { usePlugins } from '~/composables/use-plugins'
import {
  createLtcsProjectServiceMenuIndexResponseStub
} from '~~/stubs/create-ltcs-project-service-menu-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-project-service-menu-resolver-store', () => {
  const $api = createMockedApi('ltcsProjectServiceMenus')
  const plugins = createMockedPlugins({ $api })
  const emptyResponse = {
    list: [],
    pagination: {}
  }

  let store: LtcsProjectServiceMenuResolverStore

  const clearArray = (array: any[]) => {
    array.splice(0, array.length, ...[])
  }

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeEach(() => {
      jest.spyOn($api.ltcsProjectServiceMenus, 'getIndex').mockResolvedValue(emptyResponse)
      store = useLtcsProjectServiceMenuResolverStore()
    })

    afterEach(() => {
      mocked($api.ltcsProjectServiceMenus.getIndex).mockReset()
    })

    it('should have 4 values (2 states, 2 getters)', () => {
      expect(keys(store.state)).toHaveLength(4)
    })

    describe('isLoadingServiceMenus', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingServiceMenus).toBeRef()
        expect(store.state.isLoadingServiceMenus.value).toBeFalse()
      })
    })

    describe('menus', () => {
      it('should be ref to empty array', () => {
        expect(store.state.menus).toBeRef()
        expect(store.state.menus.value).toBeEmptyArray()
      })
    })

    describe('getters', () => {
      const response = createLtcsProjectServiceMenuIndexResponseStub()
      const menus = response.list

      beforeEach(() => {
        mocked($api.ltcsProjectServiceMenus.getIndex).mockResolvedValue(response)
        store = useLtcsProjectServiceMenuResolverStore()
      })

      describe('resolveLtcsProjectServiceMenuName', () => {
        beforeEach(() => {
          clearArray(store.state.menus.value)
        })

        it('should be ref to function', () => {
          expect(store.state.resolveLtcsProjectServiceMenuName).toBeRef()
          expect(store.state.resolveLtcsProjectServiceMenuName.value).toBeFunction()
        })

        it.each(menus.slice(0, 4))('should return menu\'s displayName', async x => {
          await store.update()
          expect(store.state.resolveLtcsProjectServiceMenuName.value(x)).toBe(x.displayName)
          expect(store.state.resolveLtcsProjectServiceMenuName.value(x.id)).toBe(x.displayName)
        })

        it('should return alternative value when menu not exists in state', async () => {
          await store.update()
          const id = 9999
          expect(menus.every(x => x.id !== id)).toBeTrue()
          expect(store.state.resolveLtcsProjectServiceMenuName.value(id)).toBe('-')
          expect(store.state.resolveLtcsProjectServiceMenuName.value(id, 'n/a')).toBe('n/a')
        })

        it('should be reflected state changes', async () => {
          const menu = menus[0]
          const resolvedName = computed(() => store.state.resolveLtcsProjectServiceMenuName.value(menu.id))
          expect(resolvedName.value).toBe('-')
          await store.update()
          expect(resolvedName.value).toBe(menu.displayName)
        })
      })

      describe('getLtcsProjectServiceMenuOptions', () => {
        beforeEach(() => {
          clearArray(store.state.menus.value)
        })

        it('should be ref to function', () => {
          expect(store.state.getLtcsProjectServiceMenuOptions).toBeRef()
          expect(store.state.getLtcsProjectServiceMenuOptions.value).toBeFunction()
        })

        it('should be reflected state changes', async () => {
          const expected = menus.map(x => ({
            text: x.displayName, value: x.id
          }))
          const { getLtcsProjectServiceMenuOptions } = store.state

          expect(getLtcsProjectServiceMenuOptions.value).toBeFunction()
          expect(getLtcsProjectServiceMenuOptions.value()).toBeEmptyArray()

          await store.update()

          expect(getLtcsProjectServiceMenuOptions.value()).toStrictEqual(expected)
        })
      })
    })
  })

  describe('update', () => {
    const response = createLtcsProjectServiceMenuIndexResponseStub()

    beforeAll(() => {
      jest.spyOn($api.ltcsProjectServiceMenus, 'getIndex').mockResolvedValue(response)
      store = useLtcsProjectServiceMenuResolverStore()
      mocked($api.ltcsProjectServiceMenus.getIndex).mockClear()
    })

    afterAll(() => {
      mocked($api.ltcsProjectServiceMenus.getIndex).mockReset()
    })

    beforeEach(() => {
      clearArray(store.state.menus.value)
    })

    it('should call $api.ltcsProjectServiceMenus.getIndex', async () => {
      await store.update()
      expect($api.ltcsProjectServiceMenus.getIndex).toHaveBeenCalledTimes(1)
      expect($api.ltcsProjectServiceMenus.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.isLoadingServiceMenus', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.ltcsProjectServiceMenus, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingServiceMenus.value).toBeFalse()

      const promise = store.update()

      expect(store.state.isLoadingServiceMenus.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingServiceMenus.value).toBeFalse()
    })

    it('should update state.menus', async () => {
      const xs = response.list
      expect(store.state.menus.value).not.toStrictEqual(xs)

      await store.update()

      expect(store.state.menus.value).toStrictEqual(xs)
    })
  })
})
