/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { MimeType } from '@zinger/enums/lib/mime-type'
import { DateLike } from '~/models/date'

/**
 * 障害福祉サービス：請求：ファイル.
 */
export type DwsBillingFile = Readonly<{
  /** ファイル名 */
  name: string
  /** トークン */
  token: string
  /** MimeType */
  mimeType: MimeType
  /** 作成日時 */
  createdAt: DateLike
  /** 最終ダウンロード日時 */
  downloadedAt: DateLike | undefined
}>
