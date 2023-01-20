/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, watch } from '@nuxtjs/composition-api'
import { createTaskServiceOptions } from '~/composables/create-service-options'

type Options = ReturnType<typeof createTaskServiceOptions>

/**
 * サービスオプション選択肢を使用する
 *
 * @param fn サービスオプションを取得する関数
 * @param changeCallback 選択肢の内容が変わったときに実行する関数
 */
export const useServiceOptionItems = (fn: () => Options, changeCallback?: () => void) => {
  const optionItems = computed(() => fn().map(x => {
    return {
      value: x.code,
      hint: x.hint,
      text: x.name,
      enabled: true
    }
  }))
  const hasOptionItems = computed(() => optionItems.value.length !== 0)
  watch(optionItems, (_, old) => {
    /*
     * 選択可能なサービスオプションの内容が変わった時はコールバックを実行する
     * 変更前が空の場合は何もしない
     */
    if (old.length !== 0) {
      changeCallback && changeCallback()
    }
  })

  return {
    hasOptionItems,
    optionItems
  }
}
