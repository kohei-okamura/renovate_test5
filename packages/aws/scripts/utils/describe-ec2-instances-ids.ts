/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createEc2Service } from '~aws/scripts/utils/create-ec2-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeEc2InstanceIds = async (values: string[]): Promise<string[]> => {
  const ec2 = createEc2Service()

  const data = await runAwsCommand(() => ec2.describeInstances({ Filters: [{ Name: 'tag:Name', Values: values }] }))

  const reservations = data.Reservations ?? []
  return reservations.flatMap(reservation => {
    const instances = reservation.Instances ?? []
    return instances
      .map(instance => instance.InstanceId ?? '')
      .filter(id => id !== '')
  })
}
