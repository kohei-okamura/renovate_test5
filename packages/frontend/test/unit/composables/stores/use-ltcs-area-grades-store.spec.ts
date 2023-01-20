/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { Deferred } from 'ts-deferred'
import { LtcsAreaGradesStore, useLtcsAreaGradesStore } from '~/composables/stores/use-ltcs-area-grades-store'
import { usePlugins } from '~/composables/use-plugins'
import { createLtcsAreaGradeIndexResponseStub } from '~~/stubs/create-ltcs-area-grade-index-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-ltcs-area-grades-store', () => {
  const $api = createMockedApi('ltcsAreaGrades')
  const plugins = createMockedPlugins({ $api })
  let store: LtcsAreaGradesStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useLtcsAreaGradesStore()
    })

    it('should have 2 values (2 states, 0 getter)', () => {
      expect(keys(store.state)).toHaveLength(2)
    })

    describe('ltcsAreaGrades', () => {
      it('should be ref to empty array', () => {
        expect(store.state.ltcsAreaGrades).toBeRef()
        expect(store.state.ltcsAreaGrades.value).toBeEmptyArray()
      })
    })

    describe('isLoadingLtcsAreaGrades', () => {
      it('should be ref to false', () => {
        expect(store.state.isLoadingLtcsAreaGrades).toBeRef()
        expect(store.state.isLoadingLtcsAreaGrades.value).toBeFalse()
      })
    })
  })

  describe('getIndex', () => {
    const response = createLtcsAreaGradeIndexResponseStub()

    beforeEach(() => {
      jest.spyOn($api.ltcsAreaGrades, 'getIndex').mockResolvedValue(response)
      store = useLtcsAreaGradesStore()
    })

    afterEach(() => {
      mocked($api.ltcsAreaGrades.getIndex).mockReset()
    })

    it('should call $api.ltcsAreaGrades.getIndex', async () => {
      await store.getIndex()
      expect($api.ltcsAreaGrades.getIndex).toHaveBeenCalledTimes(1)
      expect($api.ltcsAreaGrades.getIndex).toHaveBeenCalledWith({ all: true })
    })

    it('should update state.ltcsAreaGrades', async () => {
      expect(store.state.ltcsAreaGrades.value).not.toStrictEqual(response.list)
      await store.getIndex()
      expect(store.state.ltcsAreaGrades.value).toStrictEqual(response.list)
    })

    it('should update state.isLoadingLtcsAreaGrades', async () => {
      const deferred = new Deferred<typeof response>()
      jest.spyOn($api.ltcsAreaGrades, 'getIndex').mockReturnValue(deferred.promise)
      expect(store.state.isLoadingLtcsAreaGrades.value).toBeFalse()

      const promise = store.getIndex()

      expect(store.state.isLoadingLtcsAreaGrades.value).toBeTrue()
      deferred.resolve(response)
      await promise
      expect(store.state.isLoadingLtcsAreaGrades.value).toBeFalse()
    })
  })
})
