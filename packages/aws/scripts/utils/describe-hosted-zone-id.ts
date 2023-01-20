/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createRoute53Service } from '~aws/scripts/utils/create-route53-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeHostedZoneId = async (name: string): Promise<string> => {
  const route53 = createRoute53Service()

  const data = await runAwsCommand(() => route53.listHostedZones())

  const zones = data.HostedZones ?? []
  const id = zones.find(x => x.Name === `${name}.`)?.Id
  assert(typeof id === 'string', 'Failed to describe target hosted zone id')
  return id
}
