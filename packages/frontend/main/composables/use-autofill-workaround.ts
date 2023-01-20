/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ref } from '@nuxtjs/composition-api'
import { componentRef } from '~/support/reactive'

/*
 * autofill の適用有無をチェックする最大試行回数
 * 大体 5 回程度で適用されるっぽいけど、長めに設定しておく
 */
const MAX_WATCHING_TIMES = 20
/*
 * autofill の適用をチェックする間隔
 */
const WATCHING_INTERVAL = 100

/**
 * Chromium の入力欄の自動補完（autofill）を検知するために使用する
 * 現状では必要十分なので、パスワード入力欄（<input type="password">）のみを対象とする
 */
export const useAutofillWorkaround = () => {
  const autofilled = ref(false)
  const autofilledField = componentRef()

  let watchTimer: ReturnType<typeof setTimeout> | undefined
  const watchWebKitAutofill = (times: number) => {
    const detected = !!autofilledField.value?.$el.querySelector('input[type="password"]:-webkit-autofill')
    if (detected) {
      autofilled.value = true
    } else if (times < MAX_WATCHING_TIMES) {
      watchTimer = setTimeout(() => watchWebKitAutofill(times + 1), WATCHING_INTERVAL)
    }
  }
  watchTimer = setTimeout(() => watchWebKitAutofill(1), WATCHING_INTERVAL)

  // ターゲットを解放する
  const unwatchAutofill = () => {
    autofilled.value = false
    watchTimer && clearTimeout(watchTimer)
  }

  return {
    autofilled,
    autofilledField,
    unwatchAutofill
  }
}
