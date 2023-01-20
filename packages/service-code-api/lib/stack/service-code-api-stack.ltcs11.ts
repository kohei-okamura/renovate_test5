/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { APIGatewayProxyEvent, APIGatewayProxyResult } from 'aws-lambda'
import { findLtcsHomeVisitLongTermCareEntry, FindLtcsHomeVisitLongTermCareEntryParams } from '../app/find-ltcs-home-visit-long-term-care-entry'
import { formatDate } from '../utils/format-date'
import { getParams } from '../utils/get-params'
import { parseError } from '../utils/parse-error'

const parseParams = (params: any) => {
  // TODO: ちゃんとパラメータの型を検査する
  // TODO: ちゃんとパラメータを期待する型に変換する（今はすべて文字列）
  return Object.entries(params).reduce((acc, [k, v]) => {
    switch (k) {
      case 'providedIn':
        return { ...acc, [k]: formatDate(v as Date | string, 'yyyy-MM') }
      default:
        return { ...acc, [k]: v }
    }
  }, {} as FindLtcsHomeVisitLongTermCareEntryParams)
}

export const handler = async (event: APIGatewayProxyEvent): Promise<APIGatewayProxyResult> => {
  try {
    const params = getParams(event)
    const data = await findLtcsHomeVisitLongTermCareEntry(parseParams(params))
    return {
      statusCode: 200,
      headers: {
        'Cache-Control': 'max-age=86400',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    }
  } catch (error) {
    return {
      statusCode: 500,
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(parseError(error))
    }
  }
}
