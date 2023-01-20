/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { CamelToSnake, ObjectPath, ResolveObjectPath, StringReplace } from '@zinger/helpers/types'

/**
 * モデルの型定義から SQLite のレコード型定義を得るユーティリティ型.
 */
export type RecordType<T> = {
  [K in ObjectPath<T> as CamelToSnake<StringReplace<'.', '_', K>>]: ResolveObjectPath<T, K>
}
