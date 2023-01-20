/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'

export type Error = Readonly<{
  message: string
  statusCode: number
}>

export type Errors = Readonly<{
  [key: string]: Error
}>

export const errors: Errors = {
  notFound: {
    message: 'お探しのページは見つかりませんでした。',
    statusCode: HttpStatusCode.NotFound
  }
}
