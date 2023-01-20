/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { inject, InjectionKey } from '@nuxtjs/composition-api'
import { mocked } from '@zinger/helpers/testing/mocked'
import { useInjected } from '~/composables/use-injected'

jest.mock('@nuxtjs/composition-api')

describe('composables/use-injected', () => {
  it('should returns provided value', () => {
    const key: InjectionKey<number> = Symbol('key')
    const value = 517
    mocked(inject).mockReturnValue(value)

    const actual = useInjected(key)

    expect(inject).toHaveBeenCalledTimes(1)
    expect(inject).toHaveBeenCalledWith(key)
    expect(actual).toBe(value)
  })

  it('should throw error when the value not provided', () => {
    const key: InjectionKey<number> = Symbol('mcz')
    mocked(inject).mockReturnValue(undefined)

    const f = () => useInjected(key)

    expect(f).toThrowError('Symbol(mcz) is not provided')
  })
})
