/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEventBridgeService } from '~aws/scripts/utils/create-event-bridge-service'
import { describeClustersArn } from '~aws/scripts/utils/describe-clusters-arn'
import { describeEventsNetworkConfiguration } from '~aws/scripts/utils/describe-events-network-configuration'
import { describeTaskDefinitionArn } from '~aws/scripts/utils/describe-task-definition-arn'
import { getRoleArn } from '~aws/scripts/utils/get-role-arn'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import {
  ZINGER_APP_CREATE_USER_BILLING_TASK_DEFINITION,
  ZINGER_CREATE_USER_BILLING_EVENT_RULE,
  ZINGER_CREATE_USER_BILLING_EVENT_TARGET,
  ZINGER_IAM_ECS_EVENTS_ROLE_NAME
} from '~aws/variables'

type PutTargetsRequestParams = {
  rule: string
  role: string
  targetId: string
  taskDefinition: string
}

type PutTargetsRequest = (params: PutTargetsRequestParams) => Promise<AWS.EventBridge.Types.PutTargetsRequest>

const putTargetsRequest: PutTargetsRequest = async params => ({
  Rule: params.rule,
  Targets: [{
    Arn: await describeClustersArn(),
    EcsParameters: {
      LaunchType: 'FARGATE',
      TaskCount: 1,
      TaskDefinitionArn: await describeTaskDefinitionArn(params.taskDefinition),
      NetworkConfiguration: await describeEventsNetworkConfiguration(),
      PlatformVersion: '1.4.0'
    },
    Id: params.targetId,
    RoleArn: await getRoleArn(params.role)
  }]
})

const main = (options: RunCommandOptions) => runCommand(options, async () => {
  const events = createEventBridgeService()
  const params = await putTargetsRequest({
    rule: ZINGER_CREATE_USER_BILLING_EVENT_RULE,
    role: ZINGER_IAM_ECS_EVENTS_ROLE_NAME,
    targetId: ZINGER_CREATE_USER_BILLING_EVENT_TARGET,
    taskDefinition: ZINGER_APP_CREATE_USER_BILLING_TASK_DEFINITION
  })
  return await runAwsCommand(() => events.putTargets(params))
})

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

// noinspection JSIgnoredPromiseFromCall
main(options)
