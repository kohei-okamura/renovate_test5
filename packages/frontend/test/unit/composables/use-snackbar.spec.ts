/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { createMock } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import Vue from 'vue'
import { usePlugins } from '~/composables/use-plugins'
import { useSnackbar } from '~/composables/use-snackbar'
import { SnackbarService } from '~/services/snackbar-service'
import { createMockedPlugins } from '~~/test/helpers/create-mocked-plugins'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

jest.mock('~/composables/use-plugins')

describe('composables/use-snackbar', () => {
  beforeAll(() => {
    setupComposableTest()
    const $snackbar = createMock<SnackbarService>({
      config: ref({ text: '' })
    })
    const plugins = createMockedPlugins({ $snackbar })
    mocked(usePlugins).mockReturnValue(plugins)
  })

  afterAll(() => {
    mocked(usePlugins).mockClear()
  })

  it('snackbar should be updated when config.text changed', async () => {
    const { config, snackbar } = useSnackbar()
    expect(snackbar.value).toBeFalse()

    config.value = { text: 'snackbar' }
    await Vue.nextTick()
    expect(snackbar.value).toBeTrue()

    config.value = { text: '' }
    await Vue.nextTick()
    expect(snackbar.value).toBeFalse()
  })
})
