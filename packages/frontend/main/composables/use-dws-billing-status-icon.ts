/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { DwsBillingStatus } from '@zinger/enums/lib/dws-billing-status'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<{
  status: DwsBillingStatus
}>

const resolveIcon = (source: Source | undefined) => {
  switch (source?.status) {
    case DwsBillingStatus.checking:
      return $icons.statusChecking
    case DwsBillingStatus.ready:
      return $icons.statusReady
    case DwsBillingStatus.fixed:
      return $icons.statusResolved
    default:
      return $icons.statusUnknown
  }
}

export const useDwsBillingStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
