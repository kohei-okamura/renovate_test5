/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Plugins } from '~/plugins'
import { $wrapAxiosError } from '~/plugins/axios'
import { $icons } from '~/plugins/icons'
import { $datetime } from '~/services/datetime-service'

const $useFetch: Plugins['$useFetch'] = callback => {
  callback()
  return {
    fetch: () => callback(),
    fetchState: {
      error: null,
      pending: false,
      timestamp: 0
    }
  }
}

export const mocks: Partial<Plugins> = {
  $datetime,
  $icons,
  $useFetch,
  $wrapAxiosError
}
