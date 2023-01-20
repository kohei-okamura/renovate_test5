/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { pick } from '@zinger/helpers'
import { ltcsProjectServiceMenuResolverStateKey } from '~/composables/stores/use-ltcs-project-service-menu-resolver-store'
import { useInjected } from '~/composables/use-injected'

export const useLtcsProjectServiceMenuResolver = () => {
  const state = useInjected(ltcsProjectServiceMenuResolverStateKey)
  return pick(state, ['resolveLtcsProjectServiceMenuName', 'getLtcsProjectServiceMenuOptions'])
}
