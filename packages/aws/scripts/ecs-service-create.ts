/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { Task } from '~aws/scripts/params'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { describeEcsNetworkConfiguration } from '~aws/scripts/utils/describe-ecs-network-configuration'
import { describeLoadBalancers } from '~aws/scripts/utils/describe-load-balancers'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import {
  ZINGER_APP_QUEUE_TASK_DEFINITION,
  ZINGER_ECS_CLUSTER,
  ZINGER_ECS_QUEUE,
  ZINGER_ECS_SERVICE,
  ZINGER_TASK_DEFINITION,
  ZINGER_TASK_DESIRED_COUNT_PROD,
  ZINGER_TASK_DESIRED_COUNT_SANDBOX,
  ZINGER_TASK_DESIRED_COUNT_STAGING
} from '~aws/variables'

type Options = RunCommandOptions & {
  task: Task
}

const QUEUE_PARAMS = {
  serviceName: ZINGER_ECS_QUEUE,
  taskDefinition: ZINGER_APP_QUEUE_TASK_DEFINITION
}

const SERVICE_PARAMS = {
  serviceName: ZINGER_ECS_SERVICE,
  taskDefinition: ZINGER_TASK_DEFINITION
}

const createServiceRequest = async (params: Options): Promise<AWS.ECS.Types.CreateServiceRequest> => {
  const { task, profile } = params
  const paramsData = (task === 'queue')
    ? QUEUE_PARAMS
    : { ...SERVICE_PARAMS, loadBalancers: await describeLoadBalancers() }
  return {
    cluster: ZINGER_ECS_CLUSTER,
    desiredCount: (profile === 'zinger')
      ? ZINGER_TASK_DESIRED_COUNT_PROD
      : ((profile === 'zinger-staging') ? ZINGER_TASK_DESIRED_COUNT_STAGING : ZINGER_TASK_DESIRED_COUNT_SANDBOX),
    enableExecuteCommand: false,
    launchType: 'FARGATE',
    networkConfiguration: await describeEcsNetworkConfiguration(),
    platformVersion: '1.4.0',
    ...paramsData
  }
}

const main = (options: Options) => runCommand(options, async () => {
  const ecs = createEcsService()
  const params = await createServiceRequest(options)
  return await runAwsCommand(() => ecs.createService(params))
})

const options = createCommand()
  .requiredOption('-t, --task [task]', 'e.g. queue, service')
  .parse(process.argv)
  .opts<Options>()

main(options)
