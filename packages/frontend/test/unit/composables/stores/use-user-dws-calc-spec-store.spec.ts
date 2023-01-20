/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { UserDwsCalcSpecStore, useUserDwsCalcSpecStore } from '~/composables/stores/use-user-dws-calc-spec-store'
import { usePlugins } from '~/composables/use-plugins'
import { createUserDwsCalcSpecResponseStub } from '~~/stubs/create-user-dws-calc-spec-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-user-dws-calc-spec-store', () => {
  const $api = createMockedApi('userDwsCalcSpecs')
  const plugins = createMockedPlugins({ $api })
  let store: UserDwsCalcSpecStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useUserDwsCalcSpecStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('dwsCalcSpec', () => {
      it('should be ref to undefined', () => {
        expect(store.state.dwsCalcSpec).toBeRef()
        expect(store.state.dwsCalcSpec.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const userId = 517
    const response = createUserDwsCalcSpecResponseStub()

    beforeEach(() => {
      jest.spyOn($api.userDwsCalcSpecs, 'get').mockResolvedValue(response)
      store = useUserDwsCalcSpecStore()
    })

    afterEach(() => {
      mocked($api.userDwsCalcSpecs.get).mockReset()
    })

    it('should call $api.userDwsCalcSpecs.get', async () => {
      await store.get({ id, userId })
      expect($api.userDwsCalcSpecs.get).toHaveBeenCalledTimes(1)
      expect($api.userDwsCalcSpecs.get).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.dwsCalcSpec', async () => {
      expect(store.state.dwsCalcSpec.value).toBeUndefined()
      await store.get({ id, userId })
      expect(store.state.dwsCalcSpec.value).toStrictEqual(response.dwsCalcSpec)
    })
  })
})
