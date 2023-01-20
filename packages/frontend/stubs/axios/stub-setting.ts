/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createSettingResponseStub } from '~~/stubs/create-setting-stub'

/**
 * スタッフ API をスタブ化する.
 */
export const stubSetting: StubFunction = mockAdapter => mockAdapter
  .onGet('/api/setting').reply(() => [HttpStatusCode.OK, createSettingResponseStub()])
  .onPost('/api/setting').reply(() => [HttpStatusCode.Created])
  .onPut('/api/setting').reply(() => [HttpStatusCode.OK, createSettingResponseStub()])
