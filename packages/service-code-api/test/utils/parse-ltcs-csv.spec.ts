/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import path from 'path'
import { RESOURCES } from '../../lib/constants'
import { parseLtcsCsv } from '../../lib/utils/parse-ltcs-csv'

describe('parse-dws-pwsd-service-xlsx', () => {
  test.each([
    ['ltcs-201910-source.csv'],
    ['ltcs-202104-source.csv'],
    ['ltcs-202210-source.csv']
  ])('それぞれの CSV ファイルが期待通りパースできること: %s', async source => {
    const rows = await parseLtcsCsv(path.join(RESOURCES, source))
    expect(rows).toMatchSnapshot()
  })
})
