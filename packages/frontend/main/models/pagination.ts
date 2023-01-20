/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * ページネーション.
 */
export type Pagination = Readonly<{
  /** 件数 */
  count?: number

  /** 降順 */
  desc?: boolean

  /** 1ページあたりの件数 */
  itemsPerPage?: number

  /** ページ */
  page?: number

  /** 総ページ数 */
  pages?: number

  /** ソート順 */
  sortBy?: string
}>
