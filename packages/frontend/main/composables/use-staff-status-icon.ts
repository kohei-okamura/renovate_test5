/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { StaffStatus } from '@zinger/enums/lib/staff-status'
import { Staff } from '~/models/staff'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<Pick<Staff, 'status'>>

const resolveIcon = (source: Source | undefined) => {
  switch (source?.status) {
    case StaffStatus.provisional:
      return $icons.statusProgress
    case StaffStatus.active:
      return $icons.statusResolved
    case StaffStatus.retired:
      return $icons.statusRejected
    default:
      return $icons.statusUnknown
  }
}

export const useStaffStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
