/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createMock, Mocked } from '@zinger/helpers/testing/create-mock'
import { mocked } from '@zinger/helpers/testing/mocked'
import { ECS } from 'aws-sdk'
import { AWSError } from 'aws-sdk/lib/error'
import { PromiseResult, Request } from 'aws-sdk/lib/request'
import { Family, main, Options, registerTaskDefinition } from '~aws/scripts/ecs-register-task-definition'
import { createEcsService } from '~aws/scripts/utils/create-ecs-service'
import { getAccountId } from '~aws/scripts/utils/get-account-id'
import { setupAwsSdk } from '~aws/scripts/utils/setup-aws-sdk'

jest.mock('~aws/scripts/utils/create-ecs-service')
jest.mock('~aws/scripts/utils/get-account-id')
jest.mock('~aws/scripts/utils/setup-aws-sdk')

describe('ecs-register-task-definition', () => {
  let ecs: Mocked<ECS>
  let request: Request<ECS.Types.RegisterTaskDefinitionResponse, AWSError>
  let response: PromiseResult<ECS.Types.RegisterTaskDefinitionResponse, AWSError> & {
    then: undefined
  }

  beforeEach(() => {
    response = createMock()
    request = createMock()
    ecs = createMock<ECS>()

    mocked(createEcsService).mockReturnValue(ecs)
    mocked(getAccountId).mockResolvedValue('869997810708')
    mocked(setupAwsSdk).mockReturnValue()

    jest.spyOn(ecs, 'registerTaskDefinition').mockReturnValue(request)
    jest.spyOn(request, 'promise').mockResolvedValue(response)
    jest.spyOn(console, 'log').mockReturnValue()
  })

  describe('registerTaskDefinition', () => {
    it.each<[Family]>([
      ['batch'],
      ['createUserBilling'],
      ['migration'],
      ['queue'],
      ['service']
    ])('should register task: %s', async (family: Family) => {
      await registerTaskDefinition(ecs, family, {
        github: true, // AWS 認証処理をスキップする
        profile: 'zinger-sandbox',
        tag: '20221029123456-ae16de3bf'
      })

      expect(ecs.registerTaskDefinition).toHaveBeenCalledTimes(1)
      expect(mocked(ecs.registerTaskDefinition).mock.calls[0]).toMatchSnapshot()
    })
  })

  describe('main', () => {
    it('should register task definitions', async () => {
      const options: Options = {
        github: true, // AWS 認証処理をスキップする
        profile: 'zinger-sandbox',
        tag: '20221029123456-ae16de3bf'
      }
      await registerTaskDefinition(ecs, 'batch', options)
      await registerTaskDefinition(ecs, 'createUserBilling', options)
      await registerTaskDefinition(ecs, 'migration', options)
      await registerTaskDefinition(ecs, 'queue', options)
      await registerTaskDefinition(ecs, 'service', options)
      const expected = mocked(ecs.registerTaskDefinition).mock.calls
      mocked(ecs.registerTaskDefinition).mockClear()

      await main(options)

      expect(ecs.registerTaskDefinition).toHaveBeenCalledTimes(5)
      expect(mocked(ecs.registerTaskDefinition).mock.calls).toStrictEqual(expected)
    })
  })
})
