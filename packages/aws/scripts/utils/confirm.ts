/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Prompts from 'prompts'

export const confirm = async (message: string): Promise<boolean> => {
  const { value } = await Prompts({
    type: 'confirm',
    name: 'value',
    message,
    initial: false
  })
  return value
}
