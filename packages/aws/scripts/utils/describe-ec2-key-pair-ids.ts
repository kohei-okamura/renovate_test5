/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createEc2Service } from '~aws/scripts/utils/create-ec2-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeEc2KeyPairIds = async (): Promise<string[]> => {
  const ec2 = createEc2Service()

  const data = await runAwsCommand(() => ec2.describeKeyPairs())

  const keyPairs = data.KeyPairs ?? []
  return keyPairs.map(x => x.KeyPairId ?? '')
}
