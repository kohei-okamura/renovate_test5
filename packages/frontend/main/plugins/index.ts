/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { WrapAxiosErrorFunction } from '~/plugins/axios'
import { Back } from '~/plugins/back'
import { $icons } from '~/plugins/icons'
import { UseFetch } from '~/plugins/nuxt-composition-api'
import { Services } from '~/plugins/services'
import { DatetimeService } from '~/services/datetime-service'

export type Plugins = Services & Readonly<{
  $back: Back
  $datetime: DatetimeService
  $google: () => Promise<Google>
  $icons: typeof $icons
  $useFetch: UseFetch
  $wrapAxiosError: WrapAxiosErrorFunction
}>
