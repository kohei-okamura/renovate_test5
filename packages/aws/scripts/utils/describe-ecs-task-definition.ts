/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeEcsTaskDefinition = async (arn: string): Promise<AWS.ECS.TaskDefinition> => {
  const ecs = createEcsService()

  const data = await runAwsCommand(() => ecs.describeTaskDefinition({ taskDefinition: arn }))

  return data.taskDefinition ?? {}
}
