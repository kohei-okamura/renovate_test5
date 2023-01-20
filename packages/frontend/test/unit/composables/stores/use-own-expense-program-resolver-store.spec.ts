/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import {
  OwnExpenseProgramResolverStore,
  useOwnExpenseProgramResolverStore
} from '~/composables/stores/use-own-expense-program-resolver-store'
import { usePlugins } from '~/composables/use-plugins'
import { OFFICE_ID_MIN } from '~~/stubs/create-office-stub'
import { createOwnExpenseProgramIndexResponseStub } from '~~/stubs/create-own-expense-program-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-own-expense-program-resolver-store', () => {
  const $api = createMockedApi('ownExpensePrograms')
  const plugins = createMockedPlugins({ $api })
  const emptyResponse = { list: [], pagination: {} }
  let store: OwnExpenseProgramResolverStore

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
      jest.spyOn($api.ownExpensePrograms, 'getIndex').mockResolvedValueOnce(emptyResponse)
      store = useOwnExpenseProgramResolverStore()
    })

    afterEach(() => {
      mocked($api.ownExpensePrograms.getIndex).mockReset()
    })

    it('should have 3 values (2 states, 2 getters)', () => {
      expect(keys(store.state)).toHaveLength(5)
    })

    describe('isLoadingOwnExpensePrograms', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingOwnExpensePrograms).toBeRef()
        expect(store.state.isLoadingOwnExpensePrograms.value).toBeFalse()
      })
    })

    describe('partialOwnExpenseProgramOptions', () => {
      it('should be ref to empty array', () => {
        expect(store.state.ownExpensePrograms).toBeRef()
        expect(store.state.ownExpensePrograms.value).toBeEmptyArray()
      })
    })

    describe('resolveOwnExpenseProgramName', () => {
      const response = createOwnExpenseProgramIndexResponseStub()
      const { list } = response

      beforeEach(() => {
        mocked($api.ownExpensePrograms.getIndex)
          .mockReset()
          .mockResolvedValueOnce(emptyResponse)
          .mockResolvedValue(response)
        store = useOwnExpenseProgramResolverStore()
      })

      it('should be ref to function', () => {
        expect(store.state.resolveOwnExpenseProgramName).toBeRef()
        expect(store.state.resolveOwnExpenseProgramName.value).toBeFunction()
      })

      it.each(list.slice(0, 4))('should return ownExpenseProgram\'s name', async x => {
        await store.updateOwnExpenseProgramOptions()
        expect(store.state.resolveOwnExpenseProgramName.value(x)).toBe(x.name)
        expect(store.state.resolveOwnExpenseProgramName.value(x.id)).toBe(x.name)
      })

      it('should return alternative value when ownExpenseProgram not exists in state', async () => {
        await store.updateOwnExpenseProgramOptions()
        const id = 9999
        expect(list.every(x => x.id !== id)).toBeTrue()
        expect(store.state.resolveOwnExpenseProgramName.value(id)).toBe('-')
        expect(store.state.resolveOwnExpenseProgramName.value(id, 'n/a')).toBe('n/a')
      })

      it('should be reflected state changes', async () => {
        const ownExpenseProgram = list[0]
        const resolvedName = computed(() => store.state.resolveOwnExpenseProgramName.value(ownExpenseProgram.id))
        expect(resolvedName.value).toBe('-')
        await store.updateOwnExpenseProgramOptions()
        expect(resolvedName.value).toBe(ownExpenseProgram.name)
      })
    })

    describe('ownExpenseOptions', () => {
      const response = createOwnExpenseProgramIndexResponseStub()
      const { list } = response

      beforeEach(() => {
        jest.spyOn($api.ownExpensePrograms, 'getIndex')
          .mockReset()
          .mockResolvedValueOnce(emptyResponse)
          .mockResolvedValue(response)
        store = useOwnExpenseProgramResolverStore()
        clearArray(store.state.ownExpensePrograms.value)
      })

      it('should be ref to array', () => {
        expect(store.state.ownExpenseOptions).toBeRef()
        expect(store.state.ownExpenseOptions.value).toBeArray()
      })

      it('should be reflected state changes', async () => {
        const expected = list.map(x => ({ text: x.name, value: x.id }))
        const options = computed(() => store.state.ownExpenseOptions.value)
        expect(options.value).toBeEmptyArray()
        await store.updateOwnExpenseProgramOptions()
        expect(options.value).toStrictEqual(expected)
      })
    })

    describe('ownExpenseOptionsByOffice', () => {
      const response = createOwnExpenseProgramIndexResponseStub()
      const { list } = response
      const officeId = OFFICE_ID_MIN

      beforeEach(() => {
        jest.spyOn($api.ownExpensePrograms, 'getIndex')
          .mockReset()
          .mockResolvedValueOnce(emptyResponse)
          .mockResolvedValue(response)
        store = useOwnExpenseProgramResolverStore()
        clearArray(store.state.ownExpensePrograms.value)
      })

      it('should be ref to array', () => {
        expect(store.state.ownExpenseOptionsByOffice).toBeRef()
        expect(store.state.ownExpenseOptionsByOffice.value(officeId)).toBeArray()
      })

      it('should be reflected state changes', async () => {
        const expected = list
          .filter(x => !x.officeId || x.officeId === officeId)
          .map(x => ({ text: x.name, value: x.id }))
        const options = computed(() => store.state.ownExpenseOptionsByOffice.value(officeId))
        expect(options.value).toBeEmptyArray()
        await store.updateOwnExpenseProgramOptions()
        expect(options.value).toStrictEqual(expected)
      })
    })
  })

  describe('updateOwnExpensePrograms', () => {
    const response = createOwnExpenseProgramIndexResponseStub()

    beforeAll(() => {
      jest.spyOn($api.ownExpensePrograms, 'getIndex')
        .mockResolvedValue(response)
        .mockResolvedValueOnce(emptyResponse)
      store = useOwnExpenseProgramResolverStore()
    })

    afterAll(() => {
      mocked($api.ownExpensePrograms.getIndex).mockRestore()
    })

    beforeEach(() => {
      clearArray(store.state.ownExpensePrograms.value)
      mocked($api.ownExpensePrograms.getIndex).mockClear()
    })

    it('should call $api.ownExpensePrograms.getIndex', async () => {
      await store.updateOwnExpenseProgramOptions()
      expect($api.ownExpensePrograms.getIndex).toHaveBeenCalledTimes(1)
      expect($api.ownExpensePrograms.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.ownExpensePrograms', async () => {
      const expected = response.list
      expect(store.state.ownExpensePrograms.value).not.toStrictEqual(expected)
      await store.updateOwnExpenseProgramOptions()
      expect(store.state.ownExpensePrograms.value).toStrictEqual(expected)
    })

    it('should update state.isLoadingOwnExpensePrograms', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.ownExpensePrograms.getIndex).mockReturnValueOnce(deferred.promise)
      expect(store.state.isLoadingOwnExpensePrograms.value).toBeFalse()

      const promise = store.updateOwnExpenseProgramOptions()

      expect(store.state.isLoadingOwnExpensePrograms.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingOwnExpensePrograms.value).toBeFalse()
    })
  })
})
