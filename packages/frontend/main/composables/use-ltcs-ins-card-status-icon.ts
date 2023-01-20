/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { LtcsInsCardStatus } from '@zinger/enums/lib/ltcs-ins-card-status'
import { LtcsInsCard } from '~/models/ltcs-ins-card'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<Pick<LtcsInsCard, 'status'>>

const resolveIcon = (source: Source | undefined) => {
  switch (source?.status) {
    case LtcsInsCardStatus.applied:
      return $icons.statusProgress
    case LtcsInsCardStatus.approved:
      return $icons.statusResolved
    default:
      return $icons.statusUnknown
  }
}

export const useLtcsInsCardStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
