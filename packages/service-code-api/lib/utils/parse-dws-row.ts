/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { normalizeString } from './normalize-string'
import { Row } from './xlsx'

const SERVICE_DIVISION_CODE_COLUMN_INDEX = 0
const SERVICE_CATEGORY_CODE_COLUMN_INDEX = 1
const LABEL_COLUMN_INDEX = 2

type DwsRowData = {
  serviceDivisionCode: string
  serviceCategoryCode: string
  serviceCode: string
  label: string
  score: number
}

export type ParseDwsRowFunction<T> = (row: Row, scoreColumnIndex: number) => T[]

/**
 * サービスコード表（障害福祉サービス）の行から必要な情報を抽出する.
 */
export const parseDwsRow = <T> (f: (data: DwsRowData) => T[]): ParseDwsRowFunction<T> => (row, scoreColumnIndex) => {
  const serviceDivisionCode = '' + row.get(SERVICE_DIVISION_CODE_COLUMN_INDEX)
  const serviceCategoryCode = '' + row.get(SERVICE_CATEGORY_CODE_COLUMN_INDEX)
  const serviceCode = serviceDivisionCode + serviceCategoryCode
  const label = normalizeString(row.get(LABEL_COLUMN_INDEX) as string)
  const score = +row.get(scoreColumnIndex) || 0
  return f({
    serviceDivisionCode,
    serviceCategoryCode,
    serviceCode,
    label,
    score
  })
}
