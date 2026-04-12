import pluginVue from 'eslint-plugin-vue'
import vueTsEslintConfig from '@vue/eslint-config-typescript'
import prettierConfig from '@vue/eslint-config-prettier'

export default [
    // Глобальные игноры (замена .eslintignore)
    {
        ignores: [
            'dist/**',
            'node_modules/**',
            '*.min.js',
        ],
    },

    // Базовые правила для Vue SFC
    ...pluginVue.configs['flat/recommended'],

    // TypeScript поверх Vue
    ...vueTsEslintConfig(),

    // Prettier в конце (отключает конфликтующие правила форматирования)
    prettierConfig,

    // Кастомные переопределения
    {
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
]
