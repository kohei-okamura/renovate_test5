/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { promises as fs } from 'fs'

export const fileExists = async (path: string): Promise<boolean> => {
  try {
    return (await fs.lstat(path)).isFile()
  } catch (e) {
    return false
  }
}
