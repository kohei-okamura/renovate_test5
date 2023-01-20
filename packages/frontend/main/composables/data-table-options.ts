/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { camelToKebab } from '@zinger/helpers'
import { VDataTableHeader } from '~/models/vuetify'
import { ZDataTableOptions } from '~/models/z-data-table-options'

type PartialHeader = Partial<VDataTableHeader>
export type TableHeaders = ZDataTableOptions<any>['headers']

export const dataTableOptions = <T> (options: ZDataTableOptions<T>): ZDataTableOptions<T> => options

/**
 * テーブルのヘッダーを定義するためのサポート関数
 * 全てのヘッダーに以下のプロパティを追加します（個別指定が優先されます）。
 * { class: th-[header.value のキャメルケース], sortable: false }
 *
 * @param headers 各ヘッダーの定義
 * @param additional 全てのヘッダーに一括で追加したいプロパティ。個別指定が優先されます。 e.g. { align: 'start }
 * @example
 * appendHeadersCommonProperty([
 *  { text: 'id', value: 'fooId', sortable: true },
 *  { text: '名前', value: 'fooName', width: 200 },
 *  { text: '年齢', value: 'fooAge', align: 'end },
 * ], { align: 'start' })
 * //=> [
 *  { text: 'id', value: 'fooId', sortable: true, class: 'th-foo-id', align: 'start' },
 *  { text: '名前', value: 'fooName', width: 200, class: 'th-foo-name', sortable: false, align: 'start' },
 *  { text: '年齢', value: 'fooAge', align: 'end, class: 'th-foo-age', sortable: false },
 * ]
 */
export const appendHeadersCommonProperty = (headers: TableHeaders, additional: PartialHeader = {}) => {
  return headers.map(header => ({
    ...{ class: `th-${camelToKebab(header.value)}`, sortable: false },
    ...additional,
    ...header
  }))
}
