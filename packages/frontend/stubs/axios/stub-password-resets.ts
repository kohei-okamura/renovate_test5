/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'

/**
 * パスワード再設定 API をスタブ化する.
 */
export const stubPasswordResets: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/password-resets\/[a-zA-Z0-9]{60}$/).reply(HttpStatusCode.NoContent)
  .onPost('/api/password-resets').reply(HttpStatusCode.Created)
  .onPut(/\/api\/password-resets\/[a-zA-Z0-9]{60}$/).reply(HttpStatusCode.NoContent)
