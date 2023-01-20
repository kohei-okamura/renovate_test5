/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { DwsCertificationStatus } from '@zinger/enums/lib/dws-certification-status'
import { DwsCertification } from '~/models/dws-certification'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<Pick<DwsCertification, 'status'>>

const resolveIcon = (source: Source | undefined) => {
  switch (source?.status) {
    case DwsCertificationStatus.applied:
      return $icons.statusProgress
    case DwsCertificationStatus.approved:
      return $icons.statusResolved
    default:
      return $icons.statusUnknown
  }
}

export const useDwsCertificationStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
