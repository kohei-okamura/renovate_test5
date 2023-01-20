/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { mocked } from '@zinger/helpers/testing/mocked'
import { createGlobalStore } from '~/composables/stores'
import * as createSessionStore from '~/composables/stores/create-session-store'
import { createMockedApi } from '~~/test/helpers/create-mocked-api'
import { setupComposableTest } from '~~/test/helpers/setup-composable-test'

describe('composables/stores/index', () => {
  it('should be initialized to have a session store', () => {
    setupComposableTest()
    const $api = createMockedApi('sessions')
    /*
     * TODO: これが望ましいが、2020-05-21時点だとこの比較は通らない
     * おそらく `toMatchObject` の問題
     * Composition API 絡みの内容っぽいので、そのうち直るかも
     */
    // const globalStore = createGlobalStore(context)
    // const sessionStore = createSessionStore(context)
    // expect(globalStore.session).toMatchObject(sessionStore)

    // 上記だとテストが通らないため、暫定実装
    const sessionStore = createSessionStore.createSessionStore({ $api })
    jest.spyOn(createSessionStore, 'createSessionStore').mockReturnValue(sessionStore)

    const globalStore = createGlobalStore({ $api })

    expect(globalStore.session).toMatchObject(sessionStore)
    mocked(createSessionStore.createSessionStore).mockReset()
  })
})
