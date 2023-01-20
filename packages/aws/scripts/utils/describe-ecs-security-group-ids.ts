/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createEc2Service } from '~aws/scripts/utils/create-ec2-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { ZINGER_SECURITY_GROUP_ECS } from '~aws/variables'

export const describeEcsSecurityGroupIds = async (): Promise<string[]> => {
  const ec2 = createEc2Service()

  const data = await runAwsCommand(() => ec2.describeSecurityGroups())

  const groups = data.SecurityGroups ?? []
  return groups.filter(x => (x.Description ?? '').endsWith(ZINGER_SECURITY_GROUP_ECS)).map(x => x.GroupId ?? '')
}
