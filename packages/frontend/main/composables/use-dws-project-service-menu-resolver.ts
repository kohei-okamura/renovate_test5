/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { pick } from '@zinger/helpers'
import { dwsProjectServiceMenuResolverStateKey } from '~/composables/stores/use-dws-project-service-menu-resolver-store'
import { useInjected } from '~/composables/use-injected'

export const useDwsProjectServiceMenuResolver = () => {
  const state = useInjected(dwsProjectServiceMenuResolverStateKey)
  return pick(state, ['resolveDwsProjectServiceMenuName', 'getDwsProjectServiceMenuOptions'])
}
