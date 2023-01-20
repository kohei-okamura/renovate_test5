/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed } from '@nuxtjs/composition-api'
import { assign } from '@zinger/helpers'
import { date } from '~/composables/date'
import { usePlugins } from '~/composables/use-plugins'
import { DateLike, ISO_MONTH_FORMAT } from '~/models/date'
import { RefOrValue, unref } from '~/support/reactive'

export const useLookupLtcsHomeVisitLongTermCareName = (providedIn: RefOrValue<DateLike>) => {
  const { $api } = usePlugins()
  const cache: Record<string, string> = {}
  const lookupLtcsHomeVisitLongTermCareName = computed(() => async (serviceCode: string): Promise<string> => {
    if (cache[serviceCode]) {
      return cache[serviceCode]
    }
    const { dictionaryEntry } = await $api.ltcsHomeVisitLongTermCareDictionary.get({
      serviceCode,
      providedIn: date(unref(providedIn), ISO_MONTH_FORMAT)
    })
    const name = dictionaryEntry.name
    assign(cache, { [serviceCode]: name })
    return name
  })
  return { lookupLtcsHomeVisitLongTermCareName }
}
