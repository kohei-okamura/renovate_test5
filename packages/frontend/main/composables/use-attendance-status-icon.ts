/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { Attendance } from '~/models/attendance'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<Pick<Attendance, 'isCanceled' | 'isConfirmed'>>

const resolveIcon = (source: Source | undefined) => {
  if (source === undefined) {
    return $icons.statusUnknown
  } else if (source.isCanceled) {
    return $icons.statusRejected
  } else if (source.isConfirmed) {
    return $icons.statusResolved
  } else {
    return $icons.statusProgress
  }
}

export const useAttendanceStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
