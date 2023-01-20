/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Wrapper } from '@vue/test-utils'
import Vue from 'vue'
import { ValidationObserverInstance } from '~/support/validation/types'

/**
 * {@link ValidationObserverInstance} を見つける.
 */
type GetValidationObserver = <V extends Vue> (wrapper: Wrapper<V>, ref?: string) => ValidationObserverInstance

export const getValidationObserver: GetValidationObserver = (wrapper, ref = 'observer') => {
  return wrapper.findComponent({ ref }).vm as ValidationObserverInstance
}
