/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { computed, ComputedRef } from '@nuxtjs/composition-api'

/**
 * 銀行口座番号用のヒントテキストを返す.
 *
 * @param isJapanPostBank ゆうちょ銀行かどうか
 */
export function useBankAccountNumberHint (isJapanPostBank: ComputedRef<boolean>): ComputedRef<string> {
  return computed(() => isJapanPostBank.value
    ? '口座番号は末尾の1も含めて8桁で入力してください。\n通帳に記載されている番号が8桁未満の場合は、先頭に「0」を追加して合計8桁になるように入力してください。\n\n例）口座番号が 54321 の5桁の場合：00054321'
    : '口座番号は7桁で入力してください。\n通帳に記載されている番号が7桁未満の場合は、先頭に「0」を追加して合計7桁になるように入力してください。\n\n例）口座番号が 12345 の5桁の場合：0012345'
  )
}
