/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { APIGatewayProxyEvent } from 'aws-lambda'

const validate = (params: Record<string, unknown>) => {
  // TODO: とりあえず雑に Error を投げているのであとでちゃんとやる
  if (params['providedIn'] === undefined) {
    throw new Error('providedIn is required')
  }
  // TODO: providedIn 以外もやる
}

/**
 * API Gateway から渡されたクエリパラメータを取得する.
 */
export const getParams = (event: APIGatewayProxyEvent): Record<string, unknown> => {
  // foo[]=abc&foo[]=def や foo[0]=abc&foo[1]=def のように送られてきた場合
  // { foo: ['abc', 'def'] } となるように変換する.
  const arrayParams = Object.entries(event.multiValueQueryStringParameters ?? {})
    .filter(([k, _]) => k.match(/.*\[\d*\]/u))
    .reduce<Record<string, string[]>>((acc, [k, v]) => {
      const key = k.replace(/(.*)\[\d*\]/u, '$1')
      return {
        ...acc,
        [key]: (acc[key] ? [...acc[key], ...v!] : v) as string[]
      }
    }, {})
  const notArrayParams = Object.entries(event.queryStringParameters ?? {})
    .filter(([k, _]) => !k.match(/.*\[\d*\]/u))
    .reduce((acc, [k, v]) => ({ ...acc, [k]: v }), {})

  const params = {
    ...arrayParams,
    ...notArrayParams
  }
  validate(params)
  return params
}
