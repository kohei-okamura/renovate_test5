/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
const INLINE_ELEMENTS = require('eslint-plugin-vue/lib/utils/inline-non-void-elements.json')

const rules = {
  default: {
    // Require parens in arrow function arguments as needed.
    // https://eslint.org/docs/rules/arrow-parens#require-parens-in-arrow-function-arguments-arrow-parens
    'arrow-parens': ['error', 'as-needed'],

    // enforce line breaks between arguments of a function call.
    // https://eslint.org/docs/rules/function-call-argument-newline
    'function-call-argument-newline': ['error', 'consistent'],

    // Enforce consistent indentation.
    // https://eslint.org/docs/rules/indent
    indent: ['error', 2, {
      SwitchCase: 1
    }],

    // Require or disallow an empty line between class members. / ケースバイケースなので off にする.
    // https://eslint.org/docs/rules/lines-between-class-members
    'lines-between-class-members': 'off',

    // Enforce a maximum line length.
    // https://eslint.org/docs/rules/max-len
    'max-len': ['error', {
      code: 120,
      ignoreComments: true,
      ignoreTrailingComments: true,
      ignoreStrings: true,
      ignoreTemplateLiterals: true,
      ignoreRegExpLiterals: true
    }],

    // Disallow padding within blocks.
    // https://eslint.org/docs/rules/padded-blocks
    'padded-blocks': 'error',

    // Enforce a convention in module import order.
    // https://github.com/benmosher/eslint-plugin-import/blob/master/docs/rules/order.md
    'import/order': ['error', {
      // import 順を JetBrains 系 IDE の自動ソート順（アルファベット順）に合わせる.
      alphabetize: { order: 'asc', caseInsensitive: true },
      groups: [
        ['builtin', 'external', 'internal'],
        ['parent', 'sibling']
      ]
    }],

    // Disallow the use of `console`. / 現時点では console 使いたいので off にする.
    // https://eslint.org/docs/rules/no-console
    'no-console': 'off',

    // Disallow undeclared variables. / TypeScript での誤検知が多すぎるため off にする.
    // https://eslint.org/docs/rules/no-undef
    'no-undef': 'off'
  },
  typescript: {
    // Enforce dot notation whenever possible.
    // 総実行時間が倍増し、割に合わないため off にする
    // 有効にする場合は parserOptions.project の設定が必要になる
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/dot-notation.md
    'dot-notation': 'off',
    '@typescript-eslint/dot-notation': 'off',

    // エクスポートする関数等の戻り値の型を省略可能とする.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/explicit-module-boundary-types.md
    '@typescript-eslint/explicit-module-boundary-types': 'off',

    // 名前空間と同名のクラスをエクスポートする場合に誤検知されるので無効にする
    'import/namespace': 'off',

    // Require a specific member delimiter style for interfaces and type literals.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/member-delimiter-style.md
    // https://kic-yuuki.hatenablog.com/entry/2019/10/19/141601
    '@typescript-eslint/member-delimiter-style': ['error', {
      multiline: {
        delimiter: 'none',
        requireLast: true
      },
      singleline: {
        delimiter: 'comma',
        requireLast: false
      }
    }],

    // 空のインターフェース定義を許容する.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-empty-interface.md
    '@typescript-eslint/no-empty-interface': 'off',

    // 暗黙的（implicit）でない明示的（explicit）な `any` を許容する.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-explicit-any.md
    '@typescript-eslint/no-explicit-any': 'off',

    // Disallows explicit type declarations for variables or parameters initialized to a number, string, or
    // boolean.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-inferrable-types.md
    '@typescript-eslint/no-inferrable-types': ['error', {
      // 関数の引数においては許容するよう設定をデフォルト値から変更する
      ignoreParameters: true,
      ignoreProperties: false
    }],

    // 名前空間の使用を許容する.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-namespace.md
    '@typescript-eslint/no-namespace': 'off',

    // Non-null Assertion Operator を許容する.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-non-null-assertion.md
    '@typescript-eslint/no-non-null-assertion': 'off',

    // Disallow variable redeclaration.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-redeclare.md
    'no-redeclare': 'off',
    '@typescript-eslint/no-redeclare': 'error',

    // 分割代入を利用して不要なプロパティを取り除けるように ignoreRestSiblings を有効にする
    // その他の設定は @nuxtjs/eslint-config-typescript と同じものを設定
    '@typescript-eslint/no-unused-vars': ['error', {
      args: 'all',
      argsIgnorePattern: '^_',
      ignoreRestSiblings: true
    }],

    // Disallow unnecessary constructors.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-useless-constructor.md
    'no-useless-constructor': 'off',
    '@typescript-eslint/no-useless-constructor': 'error',

    // Disallow the use of require statements except in import statements.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/no-var-requires.md
    '@typescript-eslint/no-var-requires': 'off',

    // Enforce consistent spacing before function parenthesis.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/space-before-function-paren.md
    'space-before-function-paren': 'off',
    '@typescript-eslint/space-before-function-paren': 'error',

    // Require consistent spacing around type annotations.
    // https://github.com/typescript-eslint/typescript-eslint/blob/master/packages/eslint-plugin/docs/rules/type-annotation-spacing.md
    '@typescript-eslint/type-annotation-spacing': ['error', {
      before: false,
      after: true,
      overrides: {
        arrow: {
          before: true,
          after: true
        }
      }
    }]
  },
  vue: {
    // Enforce custom event names always use "kebab-case".
    // https://eslint.vuejs.org/rules/custom-event-name-casing.html
    'vue/custom-event-name-casing': ['error', {
      // `foo:bar` のような `:` 区切りのイベント名を許容する
      ignores: ['/^[a-z]+:[a-z]+(?:-[a-z]+)*$/u']
    }],

    // Enforce self-closing style.
    // https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/html-self-closing.md
    'vue/html-self-closing': ['error', {
      // HTML 要素の Self-closing を許容する.
      html: {
        void: 'never',
        normal: 'never',
        component: 'always'
      }
    }],

    // Enforce the maximum number of attributes per line.
    // https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/max-attributes-per-line.md
    'vue/max-attributes-per-line': ['error', {
      // 1行に記載できる属性値の最大数を 5 → 10 に増やす
      singleline: 10
    }],

    // Enforce a line break before and after the contents of a singleline element.
    // https://github.com/vuejs/eslint-plugin-vue/blob/master/docs/rules/singleline-html-element-content-newline.md
    'vue/singleline-html-element-content-newline': ['error', {
      // 改行なしで記述可能とする要素を独自に追加する
      ignores: [
        ...INLINE_ELEMENTS,
        'pre',
        'template',
        'textarea',
        'v-btn',
        'v-icon',
        'v-slot',
        'z-fab'
      ]
    }],

    // Enforce valid `v-slot` directives.
    // https://eslint.vuejs.org/rules/valid-v-slot.html
    'vue/valid-v-slot': ['error', {
      // `foo.bar` 形式を許容する
      allowModifiers: true
    }]
  }
}

module.exports = {
  root: true,
  env: {
    browser: true,
    node: true
  },
  parserOptions: {
    // Uncomment if enable @typescript-eslint/dot-notation
    // project: './tsconfig.json',
    sourceType: 'module'
  },
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    '@nuxtjs/eslint-config-typescript',
    'plugin:import/typescript'
  ],
  rules: rules.default,
  overrides: [
    {
      files: ['*.js', '*.ts', '*.tsx', '*.vue'],
      rules: rules.typescript
    },
    {
      files: ['*.vue'],
      rules: rules.vue
    }
  ]
}
