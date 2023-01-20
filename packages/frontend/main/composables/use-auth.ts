/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ComputedRef, Ref } from '@nuxtjs/composition-api'
import { Permission } from '@zinger/enums/lib/permission'
import Vue from 'vue'
import { SessionStore } from '~/composables/stores/create-session-store'
import { sessionStoreKey } from '~/composables/stores/use-session-store'
import { useInjected } from '~/composables/use-injected'
import { computedWith } from '~/support/reactive'

type UseAuth = {
  isAuthenticated: ComputedRef<boolean>
  isAuthorized: Ref<(permissions?: Permission[]) => boolean>
  permissions: typeof Permission
  prepare: () => void
}

export function useAuth (session: SessionStore = useInjected(sessionStoreKey)): UseAuth {
  const isAuthenticated = computed(() => session.state.auth.value !== undefined)

  /**
   * 認証情報が取得できていない場合 => false.
   * ユーザーがシステム管理者 or 必要な権限の指定がない場合 => true.
   * 引数で指定された権限をユーザーが一つでも持っている場合 => true.
   *
   * @param permissions 必要な権限を指定する（権限による制限が必要ない場合は何も渡さない）
   */
  const isAuthorized = computedWith(session.state.auth, auth => (permissions?: Permission[]) => {
    return auth !== undefined && (
      auth.isSystemAdmin ||
      permissions === undefined ||
      permissions.length === 0 ||
      permissions.some(v => auth.permissions.includes(v))
    )
  })
  const permissions = Permission

  const prepare = async () => {
    if (session.state.auth.value === undefined) {
      try {
        await session.get()
        await Vue.nextTick()
      } catch {
        // ここで catch しないと未認証でページを開いた時に一瞬エラー画面が出る
      }
    }
  }

  return {
    isAuthenticated,
    isAuthorized,
    permissions,
    prepare
  }
}
