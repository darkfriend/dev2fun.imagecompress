import {defineConfig, loadEnv} from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'node:path'
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'

const DIST = path.resolve(__dirname, '../../../js/dev2fun.imagecompress/vue')
const SRC = path.resolve(__dirname, 'src')
const BASE = '/bitrix/js/dev2fun.imagecompress/vue/'

export default defineConfig(({mode}) => {
    const isProd = mode === 'production'
    loadEnv(mode, process.cwd(), '')

    return {
        base: BASE,

        plugins: [
            vue(),
        ],

        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
                '@': SRC,
                '@svg': path.join(SRC, 'assets/svg'),
            },
            extensions: ['.ts', '.tsx', '.js', '.vue', '.json', '.scss'],
        },

        css: {
            postcss: {
                plugins: [tailwindcss, autoprefixer],
            },
        },

        define: {
            __PROJECT: JSON.stringify(process.env.PROJECT ?? ''),
            __PROJECT_DIR: JSON.stringify(process.env.DIR ?? ''),
            __PROJECT_TYPE: JSON.stringify(process.env.TYPE ?? ''),
            __VUE_OPTIONS_API__: 'true',
            __VUE_PROD_DEVTOOLS__: 'false',
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
            'process.env.NODE_ENV': JSON.stringify(mode),
            'process.env.BASE_URL': JSON.stringify('/personal'),
        },

        build: {
            outDir: DIST,
            emptyOutDir: true,
            sourcemap: !isProd,

            minify: isProd ? 'terser' : false,
            terserOptions: isProd ? {
                ecma: 2015,
                compress: {passes: 2, drop_console: true},
                format: {comments: false},
            } : undefined,

            rollupOptions: {
                input: path.join(SRC, 'main.ts'),

                output: {
                    format: 'es',

                    entryFileNames: 'js/[name].bundle.js',
                    chunkFileNames: 'js/[name].[hash:8].chunk.js',
                    assetFileNames: (info) => {
                        const name = info.name ?? ''
                        if (/\.(woff2?|ttf|eot|otf)(\?.*)?$/.test(name)) return 'fonts/[name][extname]'
                        if (/\.(png|jpe?g|gif|ico)$/.test(name)) return 'images/[name][extname]'
                        if (/\.svg$/.test(name)) return 'images/svg/[name][extname]'
                        if (/\.css$/.test(name)) return 'css/bundle.css'
                        return 'assets/[name][extname]'
                    },

                    manualChunks: isProd
                        ? (id) => {
                            if (id.includes('node_modules')) {
                                const match = id.match(/node_modules[\\/](.+?)[\\/]/)
                                const pkg = match?.[1]?.replace('@', '') ?? 'vendor'
                                return `npm.${pkg}`
                            }
                        }
                        : undefined,
                },
            },
        },

        server: {
            port: 5173,
        },
    }
})
