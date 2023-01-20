/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ComputedRef } from '@nuxtjs/composition-api'
import { memoize } from '@zinger/helpers/lib/memoize'
import { usePlugins } from '~/composables/use-plugins'
import { DateLike, ISO_DATE_FORMAT } from '~/models/date'
import { LtcsHomeVisitLongTermCareDictionaryEntry } from '~/models/ltcs-home-visit-long-term-care-dictionary-entry'
import { OfficeId } from '~/models/office'
import { LtcsHomeVisitLongTermCareDictionaryApi } from '~/services/api/ltcs-home-visit-long-term-care-dictionary-api'
import { RefOrValue, unref } from '~/support/reactive'

type Entry = LtcsHomeVisitLongTermCareDictionaryEntry
type Params = Partial<LtcsHomeVisitLongTermCareDictionaryApi.GetIndexParams>

type LtcsHomeVisitLongTermCareDictionary = {
  lookupLtcsHomeVisitLongTermCareDictionary: ComputedRef<(serviceCode: string) => Promise<string>>
  searchLtcsHomeVisitLongTermCareDictionary: ComputedRef<(params: Params) => Promise<Entry[]>>
}

export const useLtcsHomeVisitLongTermCareDictionary = (
  officeId: RefOrValue<OfficeId>,
  isEffectiveOn: RefOrValue<DateLike>
): LtcsHomeVisitLongTermCareDictionary => {
  const { $api, $datetime } = usePlugins()
  const search = computed(() => memoize({
    async: true,
    fn: async (params: Params): Promise<Entry[]> => {
      const { list } = await $api.ltcsHomeVisitLongTermCareDictionary.getIndex({
        officeId: unref(officeId),
        isEffectiveOn: $datetime.parse(unref(isEffectiveOn)).toFormat(ISO_DATE_FORMAT),
        ...params
      })
      return list
    }
  }))
  const lookup = computed(() => async (serviceCode: string): Promise<string> => {
    const list = await search.value({ q: serviceCode })
    return list.length === 1 ? list[0].name : ''
  })
  return {
    lookupLtcsHomeVisitLongTermCareDictionary: lookup,
    searchLtcsHomeVisitLongTermCareDictionary: search
  }
}
