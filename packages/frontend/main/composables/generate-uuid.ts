/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export const generateUuid = () => {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.split('').map((v: string) => {
    switch (v) {
      case 'x':
        return Math.floor(Math.random() * 16).toString(16)
      case 'y':
        return (Math.floor(Math.random() * 4) + 8).toString(16)
      default:
        return v
    }
  }).join('')
}
