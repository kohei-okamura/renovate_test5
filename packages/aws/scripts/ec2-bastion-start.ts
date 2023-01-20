/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEc2Service } from '~aws/scripts/utils/create-ec2-service'
import { describeEc2InstanceIds } from '~aws/scripts/utils/describe-ec2-instances-ids'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'

const startInstancesRequest = async (): Promise<AWS.EC2.Types.StartInstancesRequest> => ({
  InstanceIds: await describeEc2InstanceIds(['ZingerBastion'])
})

const main = (options: RunCommandOptions) => runCommand(options, async () => {
  const ec2 = createEc2Service()
  const params = await startInstancesRequest()
  return await runAwsCommand(() => ec2.startInstances(params))
})

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)
