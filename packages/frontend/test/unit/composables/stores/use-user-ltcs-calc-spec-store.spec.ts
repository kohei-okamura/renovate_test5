/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { UserLtcsCalcSpecStore, useUserLtcsCalcSpecStore } from '~/composables/stores/use-user-ltcs-calc-spec-store'
import { usePlugins } from '~/composables/use-plugins'
import { createUserLtcsCalcSpecResponseStub } from '~~/stubs/create-user-ltcs-calc-spec-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-user-ltcs-calc-spec-store', () => {
  const $api = createMockedApi('userLtcsCalcSpecs')
  const plugins = createMockedPlugins({ $api })
  let store: UserLtcsCalcSpecStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useUserLtcsCalcSpecStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('ltcsCalcSpec', () => {
      it('should be ref to undefined', () => {
        expect(store.state.ltcsCalcSpec).toBeRef()
        expect(store.state.ltcsCalcSpec.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const userId = 517
    const response = createUserLtcsCalcSpecResponseStub()

    beforeEach(() => {
      jest.spyOn($api.userLtcsCalcSpecs, 'get').mockResolvedValue(response)
      store = useUserLtcsCalcSpecStore()
    })

    afterEach(() => {
      mocked($api.userLtcsCalcSpecs.get).mockReset()
    })

    it('should call $api.userLtcsCalcSpecs.get', async () => {
      await store.get({ id, userId })
      expect($api.userLtcsCalcSpecs.get).toHaveBeenCalledTimes(1)
      expect($api.userLtcsCalcSpecs.get).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.ltcsCalcSpec', async () => {
      expect(store.state.ltcsCalcSpec.value).toBeUndefined()
      await store.get({ id, userId })
      expect(store.state.ltcsCalcSpec.value).toStrictEqual(response.ltcsCalcSpec)
    })
  })
})
