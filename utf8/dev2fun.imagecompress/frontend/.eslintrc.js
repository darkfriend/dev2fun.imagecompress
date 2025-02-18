require('@rushstack/eslint-patch/modern-module-resolution')

const path = require('node:path')
const createAliasSetting = require('@vue/eslint-config-airbnb/createAliasSetting')

module.exports = {
    root: true,
    env: {
        browser: true,
        node: true,
    },
    parser: '@babel/eslint-parser',
    plugins: ['import', 'vue', 'require'],
    extends: [
        // add more generic rulesets here, such as:
        // 'eslint:recommended',
        // 'eslint:recommended',
        // 'plugin:vue/vue3-recommended',
        'prettier',
        'plugin:vue/vue3-essential',
        '@vue/eslint-config-airbnb',
        // 'plugin:vue/recommended' // Use this if you are using Vue.js 2.x.
    ],
    rules: {
        "indent": ["error", 4],
        'prettier/prettier': [
            'error',
            {
                endOfLine: 'auto',
            },
        ],
        'import/no-unresolved': 'error',
    },
    settings: {
        ...createAliasSetting({
            '@': `${path.resolve(__dirname, './src')}`,
        }),
    },
}
