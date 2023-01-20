/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeTaskDefinitionArn = async (taskDefinition: string): Promise<string> => {
  const ecs = createEcsService()

  const data = await runAwsCommand(() => ecs.describeTaskDefinition({
    taskDefinition
  }))

  const arn = data.taskDefinition?.taskDefinitionArn
  assert(typeof arn === 'string', 'Failed to describe task definition arn')
  return arn
}
