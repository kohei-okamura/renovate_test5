/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { getAwsConfigOptions } from '~aws/lib/get-aws-config-options'
import { Profile } from '~aws/scripts/params'

type SetupAwsSdkParams = {
  profile: Profile
}

export const setupAwsSdk = ({ profile }: SetupAwsSdkParams) => {
  const awsConfigOptions = getAwsConfigOptions(profile)
  AWS.config.credentials = new AWS.Credentials(awsConfigOptions.credentialsOptions)
  AWS.config.region = awsConfigOptions.region
}
