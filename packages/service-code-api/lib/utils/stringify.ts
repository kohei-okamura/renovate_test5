/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * JavaScript オブジェクトを文字列化する.
 */
export const stringify = (x: unknown): string => {
  const data = JSON.stringify(x, null, 2)
  return data.replace(/^( +)"(.+)": /gm, '$1$2: ')
}
