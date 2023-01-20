/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import {
  DwsProjectServiceMenuResolverStore,
  useDwsProjectServiceMenuResolverStore
} from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { usePlugins } from '~/composables/use-plugins'
import {
  createDwsProjectServiceMenuIndexResponseStub
} from '~~/stubs/create-dws-project-service-menu-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-project-service-menu-resolver-store', () => {
  const $api = createMockedApi('dwsProjectServiceMenus')
  const plugins = createMockedPlugins({ $api })
  const emptyResponse = {
    list: [],
    pagination: {}
  }

  let store: DwsProjectServiceMenuResolverStore

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
      jest.spyOn($api.dwsProjectServiceMenus, 'getIndex').mockResolvedValue(emptyResponse)
      store = useDwsProjectServiceMenuResolverStore()
    })

    afterEach(() => {
      mocked($api.dwsProjectServiceMenus.getIndex).mockReset()
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
      const response = createDwsProjectServiceMenuIndexResponseStub()
      const menus = response.list

      beforeEach(() => {
        mocked($api.dwsProjectServiceMenus.getIndex).mockResolvedValue(response)
        store = useDwsProjectServiceMenuResolverStore()
      })

      describe('resolveDwsProjectServiceMenuName', () => {
        beforeEach(() => {
          clearArray(store.state.menus.value)
        })

        it('should be ref to function', () => {
          expect(store.state.resolveDwsProjectServiceMenuName).toBeRef()
          expect(store.state.resolveDwsProjectServiceMenuName.value).toBeFunction()
        })

        it.each(menus.slice(0, 4))('should return category\'s displayName', async x => {
          await store.update()
          expect(store.state.resolveDwsProjectServiceMenuName.value(x)).toBe(x.displayName)
          expect(store.state.resolveDwsProjectServiceMenuName.value(x.id)).toBe(x.displayName)
        })

        it('should return alternative value when category not exists in state', async () => {
          await store.update()
          const id = 9999
          expect(menus.every(x => x.id !== id)).toBeTrue()
          expect(store.state.resolveDwsProjectServiceMenuName.value(id)).toBe('-')
          expect(store.state.resolveDwsProjectServiceMenuName.value(id, 'n/a')).toBe('n/a')
        })

        it('should be reflected state changes', async () => {
          const category = menus[0]
          const resolvedName = computed(() => store.state.resolveDwsProjectServiceMenuName.value(category.id))
          expect(resolvedName.value).toBe('-')
          await store.update()
          expect(resolvedName.value).toBe(category.displayName)
        })
      })

      describe('getDwsProjectServiceMenuOptions', () => {
        beforeEach(() => {
          clearArray(store.state.menus.value)
        })

        it('should be ref to function', () => {
          expect(store.state.getDwsProjectServiceMenuOptions).toBeRef()
          expect(store.state.getDwsProjectServiceMenuOptions.value).toBeFunction()
        })

        it('should be reflected state changes', async () => {
          const expected = response.list.map(x => ({
            text: x.displayName, value: x.id
          }))
          const { getDwsProjectServiceMenuOptions } = store.state

          expect(getDwsProjectServiceMenuOptions.value).toBeFunction()
          expect(getDwsProjectServiceMenuOptions.value()).toBeEmptyArray()

          await store.update()

          expect(getDwsProjectServiceMenuOptions.value()).toStrictEqual(expected)
        })
      })
    })
  })

  describe('update', () => {
    const response = createDwsProjectServiceMenuIndexResponseStub()

    beforeAll(() => {
      jest.spyOn($api.dwsProjectServiceMenus, 'getIndex').mockResolvedValue(response)
      store = useDwsProjectServiceMenuResolverStore()
      mocked($api.dwsProjectServiceMenus.getIndex).mockClear()
    })

    afterAll(() => {
      mocked($api.dwsProjectServiceMenus.getIndex).mockReset()
    })

    beforeEach(() => {
      clearArray(store.state.menus.value)
    })

    it('should call $api.dwsProjectServiceCategories.getIndex', async () => {
      await store.update()
      expect($api.dwsProjectServiceMenus.getIndex).toHaveBeenCalledTimes(1)
      expect($api.dwsProjectServiceMenus.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.isLoadingServiceCategories', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.dwsProjectServiceMenus, 'getIndex').mockReturnValue(deferred.promise)
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
