/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createIamService } from '~aws/scripts/utils/create-iam-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeS3FullAccessPolicyArn = async (): Promise<string> => {
  const iam = createIamService()

  const data = await runAwsCommand(() => iam.listPolicies())

  const policies = data.Policies ?? []
  const arn = policies.find(x => x.PolicyName === 'AmazonS3FullAccess')?.Arn
  assert(typeof arn === 'string', 'Failed to describe policy arn')
  return arn
}
