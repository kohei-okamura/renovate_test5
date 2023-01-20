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
import { withConfirm } from '~aws/scripts/utils/with-confirm'

const stopServiceRequest = async (): Promise<AWS.EC2.Types.StopInstancesRequest> => ({
  InstanceIds: await describeEc2InstanceIds(['ZingerBastion'])
})

const main = (options: RunCommandOptions) => runCommand(options, async () => await withConfirm(
  'STOP EC2 BASTION?',
  async () => {
    const ec2 = createEc2Service()
    const params = await stopServiceRequest()
    return await runAwsCommand(() => ec2.stopInstances(params))
  }
))

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)
