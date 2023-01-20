/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Stack,
  StackProps,
  aws_iam as iam
} from 'aws-cdk-lib'
import { Construct } from 'constructs'

export type GithubActionsStackProps = StackProps & {
  github: {
    owner: string
    repo: string
  }
  managedPolicies: iam.IManagedPolicy[]
  oidcProviderId: string
  roleId: string
  roleName: string
}

/**
 * GitHub Actions 用 OIDC Provider & IAM Role.
 *
 * See below:
 * - https://dev.classmethod.jp/articles/github-actions-configure-aws-credentials-oidc/
 * - https://qiita.com/takaaki_inada/items/2028328231d1085fa561
 * - https://stackoverflow.com/questions/69247498/how-can-i-calculate-the-thumbprint-of-an-openid-connect-server
 */
export class GithubActionsStack extends Stack {
  constructor (scope: Construct, id: string, props: GithubActionsStackProps) {
    super(scope, id, props)

    const { github, managedPolicies, oidcProviderId, roleId, roleName } = props

    //
    // OIDC Provider.
    //
    const oidcProvider = new iam.OpenIdConnectProvider(this, oidcProviderId, {
      url: 'https://token.actions.githubusercontent.com',
      clientIds: ['sts.amazonaws.com'],
      thumbprints: ['6938fd4d98bab03faadb97b34396831e3780aea1']
    })

    //
    // IAM Role.
    //
    new iam.Role(this, roleId, {
      assumedBy: new iam.FederatedPrincipal(
        oidcProvider.openIdConnectProviderArn,
        {
          StringLike: {
            'token.actions.githubusercontent.com:sub': `repo:${github.owner}/${github.repo}:*`
          }
        },
        'sts:AssumeRoleWithWebIdentity'
      ),
      managedPolicies,
      roleName
    })
  }
}
