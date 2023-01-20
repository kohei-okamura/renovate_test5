/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { mocked } from '@zinger/helpers/testing/mocked'
import { DateTime } from 'luxon'
import { age } from '~/composables/age'

describe('composables/age', () => {
  beforeAll(() => {
    jest.spyOn(DateTime, 'local').mockReturnValue(DateTime.fromISO('2020-05-22', { locale: 'ja' }))
  })

  afterAll(() => {
    mocked(DateTime.local).mockReset()
  })

  it('should be return "-" when argument is falsy ', () => {
    expect(age('')).toEqual('-')
  })

  it('should be return current age when passed a valid date string', () => {
    expect(age('2000-05-22')).toEqual('20')
  })

  it('should be return current age when passed a luxon DateTime', () => {
    expect(age(DateTime.fromISO('2000-05-22', { locale: 'ja' }))).toEqual('20')
  })
})
