/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createEventBridgeService } from '~aws/scripts/utils/create-event-bridge-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeTargetIds = async (rule: string): Promise<string[]> => {
  const events = createEventBridgeService()

  const data = await runAwsCommand(() => events.listTargetsByRule({
    Rule: rule
  }))

  const targets = data.Targets ?? []
  return targets.map(x => x.Id ?? '')
}
