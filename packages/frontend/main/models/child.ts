/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateLike } from '~/models/date'
import { StructuredName } from '~/models/structured-name'

/**
 * 児童.
 */
export type Child = Readonly<{
  /** 氏名 */
  name: StructuredName

  /** 生年月日 */
  birthday: DateLike
}>
