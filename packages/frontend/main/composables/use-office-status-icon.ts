/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { OfficeStatus } from '@zinger/enums/lib/office-status'
import { Office } from '~/models/office'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<Pick<Office, 'status'>>

const resolveIcon = (source: Source | undefined) => {
  switch (source?.status) {
    case OfficeStatus.inPreparation:
      return $icons.statusProgress
    case OfficeStatus.inOperation:
      return $icons.statusResolved
    case OfficeStatus.suspended:
      return $icons.statusSuspended
    case OfficeStatus.closed:
      return $icons.statusRejected
    default:
      return $icons.statusUnknown
  }
}

export const useOfficeStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
