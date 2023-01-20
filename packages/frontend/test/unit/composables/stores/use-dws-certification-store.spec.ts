/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { DwsCertificationStore, useDwsCertificationStore } from '~/composables/stores/use-dws-certification-store'
import { usePlugins } from '~/composables/use-plugins'
import { createDwsCertificationResponseStub } from '~~/stubs/create-dws-certification-response-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-dws-certification-store', () => {
  const $api = createMockedApi('dwsCertifications')
  const plugins = createMockedPlugins({ $api })
  let store: DwsCertificationStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useDwsCertificationStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('dwsCertification', () => {
      it('should be ref to undefined', () => {
        expect(store.state.dwsCertification).toBeRef()
        expect(store.state.dwsCertification.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const id = 1
    const userId = 517
    const response = createDwsCertificationResponseStub()

    beforeEach(() => {
      jest.spyOn($api.dwsCertifications, 'get').mockResolvedValue(response)
      store = useDwsCertificationStore()
    })

    afterEach(() => {
      mocked($api.dwsCertifications.get).mockReset()
    })

    it('should call $api.dwsCertifications.get', async () => {
      await store.get({ id, userId })
      expect($api.dwsCertifications.get).toHaveBeenCalledTimes(1)
      expect($api.dwsCertifications.get).toHaveBeenCalledWith({ id, userId })
    })

    it('should update state.dwsCertification', async () => {
      expect(store.state.dwsCertification.value).toBeUndefined()
      await store.get({ id, userId })
      expect(store.state.dwsCertification.value).toStrictEqual(response.dwsCertification)
    })
  })
})
