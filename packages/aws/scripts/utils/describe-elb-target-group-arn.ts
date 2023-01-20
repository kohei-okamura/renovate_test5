/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createElbService } from '~aws/scripts/utils/create-elb-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { ZINGER_TARGET_GROUP } from '~aws/variables'

export const describeElbTargetGroupArn = async (): Promise<string> => {
  const elb = createElbService()

  const data = await runAwsCommand(() => elb.describeTargetGroups())

  const groups = data.TargetGroups ?? []
  const arn = groups.find(x => x.TargetGroupName === ZINGER_TARGET_GROUP)?.TargetGroupArn
  assert(typeof arn === 'string', 'Failed to describe target group arn')
  return arn
}
