/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import * as AWS from 'aws-sdk'
import { createIamService } from '~aws/scripts/utils/create-iam-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { ZINGER_S3_USER_NAME } from '~aws/variables'

const listAccessKeysRequest = (): AWS.IAM.Types.ListAccessKeysRequest => ({
  UserName: ZINGER_S3_USER_NAME
})

export const describeAccessKeyId = async (): Promise<string> => {
  const iam = createIamService()

  const params = listAccessKeysRequest()
  const data = await runAwsCommand(() => iam.listAccessKeys(params))

  const keys = data.AccessKeyMetadata ?? []
  const accessKeyId = keys.find(x => x.UserName === ZINGER_S3_USER_NAME)?.AccessKeyId
  assert(typeof accessKeyId === 'string', 'Failed to access key id')
  return accessKeyId
}
