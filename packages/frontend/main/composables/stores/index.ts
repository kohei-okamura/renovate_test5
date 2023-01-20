/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createSessionStore } from '~/composables/stores/create-session-store'
import { NuxtContext } from '~/models/nuxt'

type CreateGlobalStoreParams = Pick<NuxtContext, '$api'>

export const createGlobalStore = (params: CreateGlobalStoreParams) => ({
  session: createSessionStore(params)
})

export type GlobalStore = ReturnType<typeof createGlobalStore>
