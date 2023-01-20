/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ValidationRuleSchema } from 'vee-validate/dist/types/types'

/**
 * 独自バリデーションルール: 全銀仕様データレコード使用可能文字.
 * @link https://www.tanshin.co.jp/business/netbk/pdf/zengin_moji.pdf
 */
export const zenginDataRecordChar: ValidationRuleSchema = {
  message: '口座名義に使用できない文字が含まれています。口座名義に間違いがないかご確認ください。',
  // eslint-disable-next-line no-irregular-whitespace
  validate: value => /^[0-9０-９a-zA-Zａ-ｚＡ-Ｚｦ-ﾟァ-ヶ 　（）()｢｣「」/／．.\-¥￥]*$/.test(value)
}
