/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { keys } from '@zinger/helpers'
import { mocked } from '@zinger/helpers/testing/mocked'
import { SettingStore, useSettingStore } from '~/composables/stores/use-setting-store'
import { usePlugins } from '~/composables/use-plugins'
import { createSettingResponseStub } from '~~/stubs/create-setting-stub'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/stores/use-setting-store', () => {
  const $api = createMockedApi('setting')
  const plugins = createMockedPlugins({ $api })
  let store: SettingStore

  beforeAll(() => {
    setupComposableTest()
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockReset()
  })

  describe('state', () => {
    beforeAll(() => {
      store = useSettingStore()
    })

    it('should have a value', () => {
      expect(keys(store.state)).toHaveLength(1)
    })

    describe('setting', () => {
      it('should be ref to undefined', () => {
        expect(store.state.organizationSetting).toBeRef()
        expect(store.state.organizationSetting.value).toBeUndefined()
      })
    })
  })

  describe('get', () => {
    const response = createSettingResponseStub()

    beforeEach(() => {
      jest.spyOn($api.setting, 'get').mockResolvedValue(response)
      store = useSettingStore()
    })

    afterEach(() => {
      mocked($api.setting.get).mockReset()
    })

    it('should call $api.setting.get', async () => {
      await store.get()
      expect($api.setting.get).toHaveBeenCalledTimes(1)
      expect($api.setting.get).toHaveBeenCalledWith()
    })

    it('should update state.organizationSetting', async () => {
      expect(store.state.organizationSetting.value).toBeUndefined()
      await store.get()
      expect(store.state.organizationSetting.value).toStrictEqual(response.organizationSetting)
    })
  })
})
