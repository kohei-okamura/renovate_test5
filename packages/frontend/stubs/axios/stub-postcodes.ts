/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'

/**
 * 郵便番号 API をスタブ化する.
 */
export const stubPostcodes: StubFunction = mockAdapter => mockAdapter
  .onGet(/^https:\/\/postcode\.eustylelab\.ninja/).passThrough()
  .onGet(/postcode\/\d+\/\d+.json/).reply(() => [HttpStatusCode.OK, [{
    city_jis_code: '13114',
    city_name: '中野区',
    city_name_kana: 'ﾅｶﾉｸ',
    prefecture_jis_code: '13',
    prefecture_name: '東京都',
    prefecture_name_kana: 'ﾄｳｷｮｳﾄ',
    town_name: '本町',
    town_name_kana: 'ﾎﾝﾁｮｳ',
    zip_code: '1640012'
  }]])
