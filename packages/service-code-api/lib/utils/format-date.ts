/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 日時を指定のフォーマットで整形する.
 */
export const formatDate = (date: Date | string, format: string) => {
  const d = new Date(date)
  return format
    .replace(/yyyy/g, `${d.getFullYear()}`)
    .replace(/MM/g, ('0' + (d.getMonth() + 1)).slice(-2))
    .replace(/dd/g, ('0' + d.getDate()).slice(-2))
    .replace(/HH/g, ('0' + d.getHours()).slice(-2))
    .replace(/mm/g, ('0' + d.getMinutes()).slice(-2))
    .replace(/ss/g, ('0' + d.getSeconds()).slice(-2))
    .replace(/SSS/g, ('00' + d.getMilliseconds()).slice(-3))
}
