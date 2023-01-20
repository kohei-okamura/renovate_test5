/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createIamService } from '~aws/scripts/utils/create-iam-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const getRoleArn = async (roleName: string): Promise<string> => {
  const iam = createIamService()

  const data = await runAwsCommand(() => iam.getRole({
    RoleName: roleName
  }))

  return data.Role.Arn
}
