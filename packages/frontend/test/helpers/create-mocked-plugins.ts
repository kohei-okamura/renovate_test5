/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMock, Mocked } from '@zinger/helpers/testing/create-mock'
import { usePlugins } from '~/composables/use-plugins'

type P = ReturnType<typeof usePlugins>

export const createMockedPlugins = (plugins: Partial<P>): Mocked<P> => createMock<P>(plugins)
