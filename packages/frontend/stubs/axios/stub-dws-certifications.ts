/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createDwsCertificationResponseStub } from '~~/stubs/create-dws-certification-response-stub'

/**
 * 障害福祉サービス受給者証 API をスタブ化する.
 */
export const stubDwsCertifications: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/users\/(\d+)\/dws-certifications\/(\d+)$/).reply(config => {
    const m = config.url!.match(/\/api\/users\/(\d+)\/dws-certifications\/(\d+)$/)
    const id = m && +m[2]
    const userId = m && +m[1]
    return id && userId
      ? [HttpStatusCode.OK, createDwsCertificationResponseStub(id)]
      : [HttpStatusCode.NotFound]
  })
  .onPost(/\/api\/users\/(\d+)\/dws-certifications$/).reply(HttpStatusCode.Created)
  .onPut(/\/api\/users\/(\d+)\/dws-certifications\/\d+$/).reply(HttpStatusCode.NoContent)
  .onDelete(/\/api\/users\/(\d+)\/dws-certifications\/\d+$/).reply(HttpStatusCode.NoContent)
