/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { LtcsBillingStatus } from '@zinger/enums/lib/ltcs-billing-status'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<{
  status: LtcsBillingStatus
}>

const resolveIcon = (source: Source | undefined) => {
  switch (source?.status) {
    case LtcsBillingStatus.checking:
      return $icons.statusChecking
    case LtcsBillingStatus.ready:
      return $icons.statusReady
    case LtcsBillingStatus.fixed:
      return $icons.statusResolved
    case LtcsBillingStatus.disabled:
      return $icons.statusDisabled
    default:
      return $icons.statusUnknown
  }
}

export const useLtcsBillingStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
