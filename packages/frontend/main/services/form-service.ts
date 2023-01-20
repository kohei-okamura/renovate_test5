/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { watch, WatchSource } from '@nuxtjs/composition-api'
import { reactive } from '@vue/composition-api'
import { onBeforeRouteLeave } from '~/composables/vue-router-compat'
import { NuxtContext } from '~/models/nuxt'

/**
 * フォーム関連の共通処理サービス.
 */
export type FormService = {
  /**
   * ページ遷移防止機能を有効にする.
   *
   * ページ遷移（ブラウザバックやタブを閉じる操作を含む）をしようとした際に
   * フォームに変更を加えている場合はダイアログを表示してユーザーに確認を求める.
   *
   * **重要**
   * page コンポーネント以外で呼び出した場合は動作しないため必ず page コンポーネントで呼び出すこと.
   */
  preventUnexpectedUnload (): void

  /**
   * フォームへの変更有無を監視する.
   */
  watch (source: WatchSource<boolean>): void

  /**
   * フォームの送信処理をラップする.
   *
   * `preventUnexpectedUnload` を用いているページにおいて
   * ページ遷移防止機能を迂回するためにページ遷移を伴う処理を本関数でラップする.
   */
  submit (f: () => void | Promise<void>): Promise<void>

  /**
   * 離脱前にページの状態を検証する.
   * フォームが変更されている場合はダイアログを表示してユーザーに確認を求める.
   *
   * @param next 離脱可能な場合に実行する関数（ページ遷移処理を想定）
   */
  verifyBeforeLeaving (next: () => void): Promise<void>
}

type CreateFormServiceParams = Pick<NuxtContext, '$confirm'>

export function createFormService ({ $confirm }: CreateFormServiceParams): FormService {
  const state = reactive({
    changed: false,
    progress: false
  })

  const handler = (event: BeforeUnloadEvent) => {
    event.preventDefault()
    event.returnValue = ''
    return ''
  }
  const activateBeforeUnloadHandler = () => window.addEventListener('beforeunload', handler)
  const deactivateBeforeUnloadHandler = () => window.removeEventListener('beforeunload', handler)

  const reset = (next: () => void) => {
    state.changed = false
    state.progress = false
    deactivateBeforeUnloadHandler()
    next()
  }
  const confirm = () => $confirm.show({
    message: 'このページを離れますか？\n\n入力中の内容はまだ保存されていません。',
    negative: 'キャンセル',
    positive: 'ページを離れる'
  })
  const shouldStopLeaving = async () => state.changed && !state.progress && !await confirm()

  return {
    preventUnexpectedUnload: () => reset(() => {
      onBeforeRouteLeave(async (_to, _from, next) => {
        await shouldStopLeaving() ? next(false) : reset(next)
      })
    }),
    watch: source => watch(
      source,
      changed => {
        state.changed = changed
        changed ? activateBeforeUnloadHandler() : deactivateBeforeUnloadHandler()
      },
      { immediate: true }
    ),
    submit: async f => {
      try {
        state.progress = true
        return await f()
      } finally {
        state.progress = false
      }
    },
    verifyBeforeLeaving: async next => {
      if (!await shouldStopLeaving()) {
        reset(next)
      }
    }
  }
}
