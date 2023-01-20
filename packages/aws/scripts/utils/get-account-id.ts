/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createStsService } from '~aws/scripts/utils/create-sts-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const getAccountId = async (): Promise<string> => {
  const sts = createStsService()

  const data = await runAwsCommand(() => sts.getCallerIdentity())

  assert(typeof data.Account === 'string', 'Failed to get account id')
  return data.Account
}
