/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Context, Middleware } from '@nuxt/types'
import { useAuth } from '~/composables/use-auth'

/**
 * 認証済みミドルウェア.
 */

type AuthenticatedMiddleware = {
  (): Middleware
}

export const authenticated: AuthenticatedMiddleware = () => async ({ app, redirect }: Context) => {
  const { session } = app.$globalStore
  const { prepare, isAuthenticated } = useAuth(session)
  await prepare()
  return isAuthenticated.value
    // 認証済みの場合はダッシュボードにリダイレクトする
    // 画面を表示させないため下記の Promise は resolve しないこと
    ? new Promise<void>(() => redirect('/dashboard'))
    : Promise.resolve()
}
