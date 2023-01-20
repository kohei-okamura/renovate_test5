/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { HttpStatusCode } from '~/models/http-status-code'
import { StubFunction } from '~~/stubs/axios/utils'
import { createInvitationStub } from '~~/stubs/create-invitation-stub'

/**
 * 招待 API をスタブ化する.
 */
export const stubInvitations: StubFunction = mockAdapter => mockAdapter
  .onGet(/\/api\/invitations\/[a-zA-Z0-9]{60}$/).reply(config => {
    const m = config.url!.match(/\/([a-zA-Z0-9]{60})$/)
    const token = m && m[1]
    if (!token) {
      return [HttpStatusCode.NotFound]
    } else if (token === 'z'.repeat(60)) {
      return [HttpStatusCode.Forbidden]
    } else {
      const invitation = createInvitationStub(1, token)
      return [HttpStatusCode.OK, { invitation }]
    }
  })
  .onPost('/api/invitations').reply(HttpStatusCode.Created)
