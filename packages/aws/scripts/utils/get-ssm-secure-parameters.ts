/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { Profile } from '~aws/scripts/params'
import { SsmRegisters } from '~aws/scripts/ssm-parameters/names'
import { zingerSsmParameters } from '~aws/scripts/ssm-parameters/zinger'
import { zingerSandboxSsmParameters } from '~aws/scripts/ssm-parameters/zinger-sandbox'
import { zingerStagingSsmParameters } from '~aws/scripts/ssm-parameters/zinger-staging'

export const getSsmSecureParameters = (profile: Profile): AWS.SSM.PutParameterRequest[] => {
  const parameters: SsmRegisters = (() => {
    switch (profile) {
      case 'zinger':
        return zingerSsmParameters
      case 'zinger-staging':
        return zingerStagingSsmParameters
      case 'zinger-sandbox':
        return zingerSandboxSsmParameters
      default:
        throw new Error(`Undefined profile given: ${profile}`)
    }
  })()
  return Object.entries(parameters).map(([name, value]) => ({
    Name: name,
    Value: value,
    Type: name.match('/zinger/secure/') ? 'SecureString' : 'String'
  }))
}
