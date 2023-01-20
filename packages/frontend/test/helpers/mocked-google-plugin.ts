/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import createGoogleMapsMock from 'jest-google-maps-mock'

/**
 * モック化された {@link Google} プラグイン ($google) を返す.
 */
export function mockedGooglePlugin (): () => Promise<Google> {
  const google: Google = {
    maps: createGoogleMapsMock()
  }
  return () => Promise.resolve(google)
}
