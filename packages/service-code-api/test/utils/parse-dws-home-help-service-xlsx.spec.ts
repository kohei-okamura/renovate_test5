/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import path from 'path'
import { RESOURCES } from '../../lib/constants'
import { parseDwsHomeHelpServiceXlsx } from '../../lib/utils/parse-dws-home-help-service-xlsx'

describe('parse-dws-home-help-service-xlsx', () => {
  test.each([
    ['dws-201910-source.xlsx', 2019],
    ['dws-202104-source.xlsx', 2021],
    ['dws-202210-source.xlsx', 2022]
  ])('それぞれのエクセルファイルが期待通りパースできること: %s', async (source, year) => {
    const rows = await parseDwsHomeHelpServiceXlsx(path.join(RESOURCES, source), year)
    expect(rows).toMatchSnapshot()
  })
})
