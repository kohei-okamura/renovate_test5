/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'

/**
 * スタッフ：メールアドレス確認 API をスタブ化する.
 */
export const stubStaffVerifications: StubFunction = mockAdapter => mockAdapter
  .onPut(/\/api\/staff-verifications\/[a-zA-Z0-9]{60}$/).reply(HttpStatusCode.NoContent)
