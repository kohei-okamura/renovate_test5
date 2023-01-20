/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Context } from '@nuxt/types'
import { Permission } from '@zinger/enums/lib/permission'
import { useAuth } from '~/composables/use-auth'

/**
 * 認証・認可ミドルウェア.
 */

type AuthMiddleware = {
  (...permissions: Permission[]): (context: Context) => Promise<void>
}

export const auth: AuthMiddleware = (...permissions) => async ({ app, redirect, route }: Context) => {
  const { session } = app.$globalStore
  const { prepare, isAuthenticated, isAuthorized } = useAuth(session)
  const redirectPromise = (path: string) => new Promise<void>(() => redirect(path))
  await prepare()
  if (!isAuthenticated.value) {
    // 未認証の場合はトップページにリダイレクトする
    // 画面を表示させないため下記の Promise は resolve しないこと
    return redirectPromise(`/?path=${route.fullPath}`)
  } else if (!isAuthorized.value(permissions)) {
    // 認可に失敗（＝必要な権限がない）場合は NotFound とする
    // 画面を表示させないため下記の Promise は resolve しないこと
    return redirectPromise('/notfound')
  } else {
    // 認証・認可に成功した場合はなにもしない
    return Promise.resolve()
  }
}
