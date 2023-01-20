/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { wait } from '@zinger/helpers'
import { Auth } from '~/models/auth'
import { HttpStatusCode } from '~/models/http-status-code'
import { SessionsApi } from '~/services/api/sessions-api'
import { StubFunction } from '~~/stubs/axios/utils'
import { createPermissionGroupStubs } from '~~/stubs/create-permission-group-stub'
import { createStaffStub } from '~~/stubs/create-staff-stub'

/**
 * 認証 API をスタブ化する.
 */
export const stubSessions: StubFunction = mockAdapter => {
  const auth = ref<Auth>()
  const staffStub = createStaffStub()

  const login = ({ isSystemAdmin = false, permissions = [], staff = staffStub }: Partial<Auth>): void => {
    auth.value = {
      isSystemAdmin,
      permissions,
      staff
    }
  }
  const logout = (): void => {
    auth.value = undefined
  }

  // email が fail で始まる場合は 400 Bad Request
  mockAdapter
    .onPost('/api/sessions', { asymmetricMatch: (actual: SessionsApi.Form) => actual.email.startsWith('fail') })
    .reply(() => [HttpStatusCode.BadRequest, { errors: { email: 'ダメだね', password: '違うね！' } }])

  // email が evil で始まる場合は 401 Unauthorized
  mockAdapter
    .onPost('/api/sessions', { asymmetricMatch: (actual: SessionsApi.Form) => actual.email.startsWith('evil') })
    .reply(HttpStatusCode.Unauthorized)

  // email が admin で始まる場合はシステム管理者としてログイン
  mockAdapter
    .onPost('/api/sessions', { asymmetricMatch: (actual: SessionsApi.Form) => actual.email.startsWith('admin') })
    .reply(async () => {
      await wait(1000)
      login({ isSystemAdmin: true })
      return [HttpStatusCode.Created, { auth: auth.value }]
    })

  // 上記に該当しない場合は一般スタッフとしてログイン
  mockAdapter
    .onPost('/api/sessions')
    .reply(() => {
      const { permissions } = createPermissionGroupStubs()[0]
      login({ permissions })
      return [HttpStatusCode.Created, { auth: auth.value }]
    })

  // ログアウト
  mockAdapter
    .onDelete('/api/sessions')
    .reply(() => {
      logout()
      return [HttpStatusCode.NoContent]
    })

  // 現在のセッションを取得
  mockAdapter
    .onGet('/api/sessions/my')
    .reply(() => {
      login({ isSystemAdmin: true })
      return [HttpStatusCode.OK, { auth: auth.value }]
    })
}
