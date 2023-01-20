/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import path from 'path'
import { RESOURCES } from '../../lib/constants'
import { parseDwsPwsdServiceXlsx } from '../../lib/utils/parse-dws-pwsd-service-xlsx'

describe('parse-dws-pwsd-service-xlsx', () => {
  test.each([
    ['dws-201910-source.xlsx'],
    ['dws-202104-source.xlsx'],
    ['dws-202210-source.xlsx']
  ])('それぞれのエクセルファイルが期待通りパースできること: %s', async source => {
    const rows = await parseDwsPwsdServiceXlsx(path.join(RESOURCES, source))
    expect(rows).toMatchSnapshot()
  })
})
