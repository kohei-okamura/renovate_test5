/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

const initFormData = {
  all: undefined,
  desc: undefined,
  itemsPerPage: undefined,
  page: 1,
  sortBy: undefined
}

export const createFormData = (query?: Record<string, unknown>) => ({
  ...initFormData,
  ...query
})
