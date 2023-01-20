/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { User } from '~/models/user'
import { $icons } from '~/plugins/icons'
import { RefOrValue, unref } from '~/support/reactive'

type Source = Partial<Pick<User, 'isEnabled'>>

const resolveIcon = (source: Source | undefined) => {
  switch (source?.isEnabled) {
    case true:
      return $icons.statusResolved
    case false:
      return $icons.statusRejected
    case undefined:
      return $icons.statusUnknown
  }
}

export const useUserStatusIcon = (source: RefOrValue<Source | undefined>) => ({
  statusIcon: computed(() => resolveIcon(unref(source)))
})
