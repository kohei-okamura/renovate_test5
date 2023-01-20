/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { ContactRelationship } from '@zinger/enums/lib/contact-relationship'

/**
 * 連絡先電話番号.
 */
export type Contact = Readonly<{
  /** 電話番号 */
  tel: string

  /** 続柄・関係 */
  relationship: ContactRelationship

  /** 名前 */
  name: string
}>
