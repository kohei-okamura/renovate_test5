/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Profile, Task } from '~aws/scripts/params'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand } from '~aws/scripts/utils/run-command'
import {
  ZINGER_APP_QUEUE_TASK_DEFINITION,
  ZINGER_ECS_CLUSTER,
  ZINGER_ECS_QUEUE,
  ZINGER_ECS_SERVICE,
  ZINGER_TASK_DEFINITION,
  ZINGER_TASK_DESIRED_COUNT_PROD,
  ZINGER_TASK_DESIRED_COUNT_STAGING
} from '~aws/variables'

type Options = {
  github: boolean
  profile: Profile
  task: Task
}

const queueParams = {
  service: ZINGER_ECS_QUEUE,
  taskDefinition: ZINGER_APP_QUEUE_TASK_DEFINITION
}

const serviceParams = {
  service: ZINGER_ECS_SERVICE,
  taskDefinition: ZINGER_TASK_DEFINITION
}

const main = (options: Options) => runCommand(options, async () => {
  const ecs = createEcsService()
  const desiredCount = (options.profile === 'zinger')
    ? ZINGER_TASK_DESIRED_COUNT_PROD
    : ZINGER_TASK_DESIRED_COUNT_STAGING
  const paramsData = (options.task === 'queue') ? queueParams : serviceParams
  return await runAwsCommand(() => ecs.updateService({
    cluster: ZINGER_ECS_CLUSTER,
    enableExecuteCommand: false,
    desiredCount,
    ...paramsData
  }))
})

const options = createCommand()
  .requiredOption('-t, --task [task]', 'e.g. queue, service')
  .parse(process.argv)
  .opts<Options>()

main(options)
