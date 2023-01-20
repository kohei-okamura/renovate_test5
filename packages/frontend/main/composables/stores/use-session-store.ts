/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { InjectionKey } from '@nuxtjs/composition-api'
import { SessionState, SessionStore } from '~/composables/stores/create-session-store'
import { usePlugins } from '~/composables/use-plugins'

export function useSessionStore () {
  const { $globalStore } = usePlugins()
  return $globalStore.session
}

export const sessionStateKey: InjectionKey<SessionState> = Symbol('sessionState')

export const sessionStoreKey: InjectionKey<SessionStore> = Symbol('sessionStore')
