/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { nextTick } from '@nuxtjs/composition-api'
import Vue from 'vue'
import { Addr } from '~/models/addr'
import { templateRef } from '~/support/reactive'

export function usePostcodeResolver (form: Partial<Addr>) {
  const streetInput = templateRef<HTMLElement>()
  const onPostcodeResolved = (addr: Addr) => {
    Vue.set(form, 'prefecture', addr.prefecture)
    Vue.set(form, 'city', addr.city)
    Vue.set(form, 'street', addr.street)
    nextTick(() => streetInput.value?.focus())
  }
  return {
    onPostcodeResolved,
    streetInput
  }
}
