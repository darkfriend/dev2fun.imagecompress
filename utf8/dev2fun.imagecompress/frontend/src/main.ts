import {createApp, defineAsyncComponent} from 'vue'
import '@/scss/style.scss'

const MoverFiles = defineAsyncComponent(() => import('@/components/MoverFiles.vue'))

const app = createApp({
    components: {
        MoverFiles
    }
})

app.config.globalProperties.appName = 'dev2fun.imagecompress'
app.config.globalProperties.appVersion = '1.0.0'
app.config.compilerOptions.whitespace = 'preserve'

app.mount('#dev2fun_imagecompress_convert_move')
