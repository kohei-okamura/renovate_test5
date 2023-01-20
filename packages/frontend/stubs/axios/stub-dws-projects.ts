/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createDwsProjectResponseStub } from '~~/stubs/create-dws-project-response-stub'

/**
 * 障害福祉サービス：計画 API をスタブ化する.
 */
export const stubDwsProjects: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/dws-projects\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/dws-projects\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    const stub = id && userId && createDwsProjectResponseStub(id)
    return stub ? [HttpStatusCode.OK, stub] : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/dws-projects$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/dws-projects\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/users\/(\d+)\/dws-projects\/\d+$/).reply(HttpStatusCode.NoContent)
