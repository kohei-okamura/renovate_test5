/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  aws_apigateway as apiGateway,
  aws_certificatemanager as certificationManager,
  aws_lambda as lambda,
  aws_lambda_nodejs as lambdaNodejs,
  aws_route53 as route53,
  aws_route53_targets as route53Targets,
  Duration,
  Stack,
  StackProps
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { DWS_HOME_HELP_SERVICE_DB_DIR, DWS_VISITING_CARE_FOR_PWSD_DB_DIR, LTCS_HOME_VISIT_LONG_TERM_CARE_DB_DIR } from '../constants'

type ServiceCodeApiStackProps = StackProps & {
  hostedZoneName: string
}

export class ServiceCodeApiStack extends Stack {
  constructor (scope: Construct, id: string, props: ServiceCodeApiStackProps) {
    super(scope, id, props)

    const { hostedZoneName } = props

    //
    // Configuration
    //
    const domainName = `service-code.${hostedZoneName}`
    const region = props.env?.region ?? 'ap-northeast-1'
    const runtime = lambda.Runtime.NODEJS_14_X

    //
    // Lambda: Layers
    //
    const dws11DbLayer = new lambda.LayerVersion(this, 'ZingerServiceCodeApiDws11DbLayer', {
      code: lambda.AssetCode.fromAsset(DWS_HOME_HELP_SERVICE_DB_DIR),
      compatibleRuntimes: [runtime]
    })
    const dws12DbLayer = new lambda.LayerVersion(this, 'ZingerServiceCodeApiDws12DbLayer', {
      code: lambda.AssetCode.fromAsset(DWS_VISITING_CARE_FOR_PWSD_DB_DIR),
      compatibleRuntimes: [runtime]
    })
    const ltcs11DbLayer = new lambda.LayerVersion(this, 'ZingerServiceCodeApiLtcs11DbLayer', {
      code: lambda.AssetCode.fromAsset(LTCS_HOME_VISIT_LONG_TERM_CARE_DB_DIR),
      compatibleRuntimes: [runtime]
    })

    //
    // Lambda: Functions
    //
    const dws11Function = new lambdaNodejs.NodejsFunction(this, 'dws11', {
      bundling: {
        // better-sqlite3 がネイティブモジュールを含むため Linux 上でバンドルする必要がある
        forceDockerBundling: process.platform !== 'linux',
        nodeModules: [
          // ネイティブモジュールを含むためここに入れないと動かない
          'better-sqlite3',
          // 動的に依存モジュールをロードしている関係でここに入れないと動かない
          'knex'
        ]
      },
      environment: {
        SERVICE_CODE_API_ASSETS: '/opt'
      },
      layers: [dws11DbLayer],
      runtime,
      timeout: Duration.seconds(29)
    })
    const dws12Function = new lambdaNodejs.NodejsFunction(this, 'dws12', {
      bundling: {
        // better-sqlite3 がネイティブモジュールを含むため Linux 上でバンドルする必要がある
        forceDockerBundling: process.platform !== 'linux',
        nodeModules: [
          // ネイティブモジュールを含むためここに入れないと動かない
          'better-sqlite3',
          // 動的に依存モジュールをロードしている関係でここに入れないと動かない
          'knex'
        ]
      },
      environment: {
        SERVICE_CODE_API_ASSETS: '/opt'
      },
      layers: [dws12DbLayer],
      runtime,
      timeout: Duration.seconds(29)
    })
    const ltcs11Function = new lambdaNodejs.NodejsFunction(this, 'ltcs11', {
      bundling: {
        // better-sqlite3 がネイティブモジュールを含むため Linux 上でバンドルする必要がある
        forceDockerBundling: process.platform !== 'linux',
        nodeModules: [
          // ネイティブモジュールを含むためここに入れないと動かない
          'better-sqlite3',
          // 動的に依存モジュールをロードしている関係でここに入れないと動かない
          'knex'
        ]
      },
      environment: {
        SERVICE_CODE_API_ASSETS: '/opt'
      },
      layers: [ltcs11DbLayer],
      runtime,
      timeout: Duration.seconds(29)
    })

    //
    // API Gateway: Routing
    //
    const api = new apiGateway.RestApi(this, 'ZingerServiceCodeApiGateway', {
      restApiName: 'service-code-api'
    })

    const dws = api.root.addResource('dws')
    dws.addResource('11').addMethod('GET', new apiGateway.LambdaIntegration(dws11Function))
    dws.addResource('12').addMethod('GET', new apiGateway.LambdaIntegration(dws12Function))
    const ltcs = api.root.addResource('ltcs')
    ltcs.addResource('11').addMethod('GET', new apiGateway.LambdaIntegration(ltcs11Function))

    //
    // Route 53: Hosted Zone
    //
    const hostedZone = route53.HostedZone.fromLookup(this, 'ZingerHostedZone', {
      domainName: hostedZoneName
    })

    //
    // Certificate
    //
    const certificate = new certificationManager.DnsValidatedCertificate(this, 'ZingerServiceCodeApiCertificate', {
      domainName,
      hostedZone,
      region
    })

    //
    // API Gateway: Custom Domain
    //
    const customDomain = new apiGateway.DomainName(this, 'ZingerServiceCodeApiCustomDomain', {
      domainName,
      certificate,
      securityPolicy: apiGateway.SecurityPolicy.TLS_1_2,
      endpointType: apiGateway.EndpointType.REGIONAL
    })
    customDomain.addBasePathMapping(api)

    //
    // Route 53: Record
    //
    // eslint-disable-next-line no-new
    new route53.ARecord(this, 'ZingerServiceCodeApiARecord', {
      zone: hostedZone,
      recordName: domainName,
      target: route53.RecordTarget.fromAlias(new route53Targets.ApiGatewayDomain(customDomain))
    })
  }
}
