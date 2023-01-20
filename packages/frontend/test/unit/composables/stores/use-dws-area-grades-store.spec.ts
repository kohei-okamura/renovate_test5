/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { DwsAreaGradesStore, useDwsAreaGradesStore } from '~/composables/stores/use-dws-area-grades-store'
import { usePlugins } from '~/composables/use-plugins'
import { createDwsAreaGradeIndexResponseStub } from '~~/stubs/create-dws-area-grade-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-area-grades-store', () => {
  const $api = createMockedApi('dwsAreaGrades')
  const plugins = createMockedPlugins({ $api })
  let store: DwsAreaGradesStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsAreaGradesStore()
    })

    it('should have 2 values (2 states, 0 getter)', () => {
      expect(keys(store.state)).toHaveLength(2)
    })

    describe('dwsAreaGrades', () => {
      it('should be ref to empty array', () => {
        expect(store.state.dwsAreaGrades).toBeRef()
        expect(store.state.dwsAreaGrades.value).toBeEmptyArray()
      })
    })

    describe('isLoadingDwsAreaGrades', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingDwsAreaGrades).toBeRef()
        expect(store.state.isLoadingDwsAreaGrades.value).toBeFalse()
      })
    })
  })

  describe('getIndex', () => {
    const response = createDwsAreaGradeIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.dwsAreaGrades, 'getIndex').mockResolvedValue(response)
      store = useDwsAreaGradesStore()
    })

    afterEach(() => {
      mocked($api.dwsAreaGrades.getIndex).mockReset()
    })

    it('should call $api.dwsAreaGrades.getIndex', async () => {
      await store.getIndex()
      expect($api.dwsAreaGrades.getIndex).toHaveBeenCalledTimes(1)
      expect($api.dwsAreaGrades.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.dwsAreaGrades', async () => {
      expect(store.state.dwsAreaGrades.value).not.toStrictEqual(response.list)
      await store.getIndex()
      expect(store.state.dwsAreaGrades.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingDwsAreaGrades', async () => {
      const deferred = new Deferred<typeof response>()
      mocked($api.dwsAreaGrades.getIndex).mockReturnValue(deferred.promise)
      expect(store.state.isLoadingDwsAreaGrades.value).toBeFalse()

      const promise = store.getIndex()

      expect(store.state.isLoadingDwsAreaGrades.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingDwsAreaGrades.value).toBeFalse()
    })
  })
})
