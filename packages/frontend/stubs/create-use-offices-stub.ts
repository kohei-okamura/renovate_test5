/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, reactive, toRefs } from '@nuxtjs/composition-api'
import { useOffices } from '~/composables/use-offices'
import { Office, OfficeId } from '~/models/office'
import { createOfficeStubs } from '~~/stubs/create-office-stub'

export const createUseOfficesStub = (offices?: Office[]): ReturnType<typeof useOffices> => {
  const options = (offices ?? createOfficeStubs()).map(x => ({ value: x.id, text: x.abbr, keyword: x.name }))
  const resolveOfficeAbbr = computed(() => (id: OfficeId | undefined, alternative = '-') => {
    return (id && options.find(x => x.value === id)?.text) ?? alternative
  })
  const data = reactive({
    isLoadingOffices: false,
    officeOptions: options
  })
  return {
    ...toRefs(data),
    resolveOfficeAbbr
  }
}
