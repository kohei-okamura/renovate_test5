/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { createIamService } from '~aws/scripts/utils/create-iam-service'
import { describeS3FullAccessPolicyArn } from '~aws/scripts/utils/describe-s3-full-access-policy-arn'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { ZINGER_S3_GROUP_NAME, ZINGER_S3_USER_NAME } from '~aws/variables'

const main = (options: RunCommandOptions) => runCommand(options, async () => {
  const iam = createIamService()
  const groupName = ZINGER_S3_GROUP_NAME
  const userName = ZINGER_S3_USER_NAME
  const policyArn = await describeS3FullAccessPolicyArn()
  return [
    await runAwsCommand(() => iam.createUser({ UserName: userName })),
    await runAwsCommand(() => iam.createGroup({ GroupName: groupName })),
    await runAwsCommand(() => iam.attachGroupPolicy({ GroupName: groupName, PolicyArn: policyArn })),
    await runAwsCommand(() => iam.addUserToGroup({ GroupName: groupName, UserName: userName })),
    await runAwsCommand(() => iam.createAccessKey({ UserName: userName }))
  ]
})

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)
