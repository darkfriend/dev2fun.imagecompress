<template>
    <div class="adm-workarea">

        <h1>{{ loc.infoTitle }}</h1>
        <div v-if="cntRows">
            <p>
                <b>
                    {{ loc.infoText }}: <code>/upload/resize_cache/webp</code>, <code>/upload/resize_cache/avif</code> {{ loc.infoIn }} <code>/upload/dev2fun.imagecompress/</code>
                </b>
            </p>
            <p>
                {{ loc.infoTextSteps }}:
            </p>
            <div>
                <ul>
                    <li>{{ loc.infoTextStep1 }}</li>
                    <li>{{ loc.infoTextStep2 }}</li>
                </ul>
            </div>
        </div>
        <AdminNote color="gray">
            <h2>{{ loc.headingStep1 }}</h2>
            <p>{{ loc.infoCountFiles }}: <b>{{ cntRows }}</b></p>
        </AdminNote>
        <AdminNote>
            <h2>{{ loc.headingStep2 }}</h2>
            <div v-for="(moveDir, key) in moveDirs">
                <p>
                    <b><code>{{ moveDir }}</code></b>
                </p>
                <p>
                    {{ loc.infoDirSize }}: {{ key === 'avif' ? avif.size : webp.size }}
                </p>
                <p>
                    {{ loc.infoDirCountFiles }}: {{ key === 'avif' ? avif.countFiles : webp.countFiles }}
                </p>
                <br>
            </div>
            <p>
                <button type="button" class="adm-btn" @click.prevent.stop="updateDataMoveDir">{{ loc.infoUpdateBtn }}</button>
            </p>
        </AdminNote>

        <AdminMessage
            v-if="error"
            :title="error"
            type="ERROR"
        />

        <div v-if="step1">
            <h2>{{ loc.headingFilesDb }}</h2>
            <AdminMessage
                v-if="step1 && moveSuccessful && !error"
                :title="loc.admMessageSuccessful"
                type="OK"
            />
            <div v-if="showProgress">
                <ProgressBar
                    :title="loc.progressBarTitle"
                    :progressText="loc.progressBarText"
                    :progress="progress"
                />
                <button class="adm-btn" @click.prevent.stop="stopProgress">{{ loc.progressBarStopBtn }}</button>
            </div>

            <div v-else-if="!step2">
                <form
                    method="post"
                    name="converted_move"
                    @submit.prevent.stop="startConvertFiles"
                >
                    <label>
                        <span>{{ loc.countFilesPerStep }}: </span>
                        <input type="number" name="count_page" step="100" max="3000" min="0" class="adm-input" v-model="countPage" />
                    </label>
                    <br>
                    <p>
                        <button type="submit" name="move" value="1" class="adm-btn adm-btn-save">
                            {{ loc.moveFilesBtn }}
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <div v-if="step2">
            <h2>{{ loc.headingFilesPhysical }}</h2>
            <AdminMessage
                v-if="errFiles.length"
                :title="errFilesString"
                type="ERROR"
            />
            <AdminMessage
                v-if="step2 && moveSuccessfulStep2 && !error"
                :title="loc.admMessageSuccessful"
                type="OK"
            />
            <div v-if="step2ShowProgress">
                <ProgressBar
                    :title="loc.progressBarTitle"
                    :progressText="step2ProgressText"
                    :progress="progress"
                />
                <button class="adm-btn" @click.prevent.stop="stopProgress">{{ loc.progressBarStopBtn }}</button>
            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, defineAsyncComponent, nextTick, shallowRef, computed } from 'vue'
import http from "@/methods/http";

// const props = defineProps({
//     loc: {
//         type: Object,
//         // default: {
//         //     infoTitle: 'Информация',
//         //     infoUpdateBtn: 'обновить информацию',
//         //     infoText: 'Необходимо перенести файлы из папки',
//         //     infoIn: 'в',
//         //     infoCountFiles: 'Найдено файлов для переноса',
//         //     infoDirSize: 'Размер папки',
//         //     infoDirCountFiles: 'Количество файлов в папке',
//         //     admMessageSuccessful: 'Все файлы успешно перенесены',
//         //     progressBarTitle: 'Перенос файлов',
//         //     progressBarText: 'Процесс переноса файлов',
//         //     progressBarStopBtn: 'Остановить',
//         //     countFilesPerStep: 'Количество картинок за шаг',
//         //     moveFilesBtn: 'Перенести файлы',
//         //     errCountFilesPerStep: 'Не указано количество картинок за шаг',
//         // }
//     }
// })

const ProgressBar = defineAsyncComponent(() => import('@/components/ProgressBar.vue'))
const AdminMessage = defineAsyncComponent(() => import('@/components/AdminMessage.vue'))
const AdminNote = defineAsyncComponent(() => import('@/components/AdminNote.vue'))

// const store = useStore()
// const loaded = ref(false)
const loc = shallowRef({
    infoTitle: '',
    infoUpdateBtn: '',
    infoText: '',
    infoTextSteps: '',
    infoTextStep1: '',
    infoTextStep2: '',
    infoIn: '',
    infoCountFiles: '',
    infoDirSize: '',
    infoDirCountFiles: '',
    admMessageSuccessful: '',
    progressBarTitle: '',
    progressBarText: '',
    progressBarStopBtn: '',
    countFilesPerStep: '',
    moveFilesBtn: '',
    errCountFilesPerStep: '',

    headingStep1: '',
    headingStep2: '',

    headingFilesDb: '',
    headingFilesPhysical: '',

    step2ProgressText: '',
    step2ProgressTextReminder: '',
})
const showProgress = ref(false)
const step2ShowProgress = ref(false)
const moveSuccessful = ref(false)
const countPage = ref(100)
const cntRows = ref(0)
const progress = ref(0)
const error = ref('')
const cntPictures = ref(0)
// const moveDir = ref('')
const moveDirs = ref({})
const currentDirSize = ref('? Mb')
const currentCountFiles = ref('?')

const step1 = ref(true)
const step2 = ref(false)
const moveSuccessfulStep2 = ref(false)
const step2ProgressText = ref('')
const stopped = ref(false)
const errFiles = ref([])

const webp = ref({
    // moveDir: '',
    size: '? Mb',
    countFiles: '?',
    cntPictures: '?',
});
const avif = ref({
    // moveDir: '',
    size: '? Mb',
    countFiles: '?',
    cntPictures: '?',
});

// const abortController = new AbortController()

const load = async () => {
    try {
        BX.showWait()
        let response = await http.get('', {
            params: {
                action: 'init',
            },
            // signal: abortController.signal,
        })
        console.log(response)
        if (!response.success) {
            throw new Error(response?.msg ?? 'Unknown error')
        }
        cntRows.value = response?.body?.cntPictures ?? 0
        moveDirs.value = response?.body?.moveDirs ?? {}

        // loaded.value = true
    } catch (e: any) {
        console.error(e)
        error.value = e.message
    } finally {
        BX.closeWait()
    }
}

const updateDataMoveDir = async () => {
    try {
        BX.showWait()

        let response = await http.get(
            '',
            {
                params: {
                    action: 'updateDataMoveDir',
                },
                // signal: abortController.signal,
            }
        )

        if (!(response?.success ?? false)) {
            throw new Error(response?.msg ?? 'Unknown error')
        }

        // currentDirSize.value = response?.body?.currentDirSize ?? '0 Mb'
        // currentCountFiles.value = response?.body?.currentCountFiles ?? 0

        // webp.value = {
        //     moveDir: '',
        //     dirSize: response?.body?.currentDirSize ?? '0 Mb',
        //     countFiles: response?.body?.currentCountFiles ?? 0,
        //     cntPictures: '?',
        // }

        webp.value.size = response?.body?.webp?.size ?? '0 Mb'
        webp.value.countFiles = response?.body?.webp?.countFiles ?? 0

        avif.value.size = response?.body?.avif?.size ?? '0 Mb'
        avif.value.countFiles = response?.body?.avif?.countFiles ?? 0

        // loaded.value = true
    } catch (e: any) {
        console.error(e)
        error.value = e.message
    } finally {
        BX.closeWait()
    }
}

const startConvertFiles = async () => {
    error.value = ''
    if (!countPage.value) {
        error.value = props.loc.errCountFilesPerStep
    }

    stopped.value = false
    progress.value = 0

    try {
        BX.showWait()
        let response = await http.post(
            '',
            {
                action: 'startConvert',
                countPage: countPage.value,
            },
            {
                // signal: abortController.signal,
            }
        )
        console.log(response)
        showProgress.value = true
        // loaded.value = true
        cntRows.value = response?.body?.cntPictures ?? 0
        cntPictures.value = response?.body?.cntPictures ?? 0

        // webp.value.cntPictures = response?.body?.webp?.cntPictures ?? 0
        // avif.value.cntPictures = response?.body?.avif?.cntPictures ?? 0

        nextTick(() => {
            if (cntRows.value) {
                progressConvertFiles()
            } else {
                moveSuccessful.value = true
            }
        })

    } catch (e: any) {
        console.error(e)
        error.value = e.message
        showProgress.value
    } finally {
        BX.closeWait()
    }
}

const progressConvertFiles = async () => {

    try {
        BX.showWait()

        let steps = Math.ceil(cntRows.value / countPage.value)
        for (let i = 1; i <= steps; i++) {
            if (!showProgress.value || stopped.value) {
                break
            }
            let response = await http.post(
                '',
                {
                    action: 'convertMove',
                    countPage: countPage.value,
                },
                {
                    // signal: abortController.signal,
                }
            )
            console.log(response)
            if (!(response?.success ?? false)) {
                throw new Error(response?.msg ?? 'Unknown error')
            }

            progress.value = Math.round((100 / steps) * i)
            console.log('progress.value', progress.value)
        }

        if (showProgress.value) {
            moveSuccessful.value = true
        }
        // loaded.value = true
    } catch (e: any) {
        console.error(e)
        error.value = e.message
    } finally {
        BX.closeWait()
        currentDirSize.value = '? Mb'
        currentCountFiles.value = '?'
        showProgress.value = false
    }

    if (!error.value && !stopped.value) {
        nextTick(() => {
            // progressMoveConvertFiles()
            progressAnalysisPhysicalFiles()
        })
    }
}

const cntMoveConvertedFiles = ref(0)
const cntPhysicalFiles  = ref(0)

/**
 * Анализ количества физических файлов
 */
const progressAnalysisPhysicalFiles = async () => {
    try {
        BX.showWait()
        step2.value = true
        cntPhysicalFiles.value = 0
        step2ShowProgress.value = true
        step2ProgressText.value = loc.value.step2ProgressText
        progress.value = 0

        let response = await http.get(
            '',
            {
                params: {
                    action: 'analysisPhysicalFiles',
                },
                // signal: abortController.signal,
            }
        )

        if (!(response?.success ?? false)) {
            throw new Error(response?.msg ?? 'Unknown error')
        }

        cntPhysicalFiles.value = response?.body?.cntFiles ?? 0
        // let steps =
        // progress.value = Math.ceil(cntPhysicalFiles.value / cntMoveConvertedFiles.value ? cntMoveConvertedFiles.value : 1)
        progress.value = Math.round((100 / cntPhysicalFiles.value) * cntMoveConvertedFiles.value)

        if (!stopped.value) {
            await progressMoveConvertFiles()
        }

        // loaded.value = true
    } catch (e: any) {
        console.error(e)
        error.value = e.message
        cntPhysicalFiles.value = 0
    } finally {
        BX.closeWait()
    }
}

const progressMoveConvertFiles = async () => {

    try {
        BX.showWait()

        step2ProgressText.value = `${loc.value.step2ProgressTextReminder}: ${cntPhysicalFiles.value}`
        let steps = Math.ceil(cntPhysicalFiles.value / countPage.value)
        console.log('steps', steps)
        for (let i = 1; i <= steps; i++) {
            if (!step2ShowProgress.value || stopped.value) {
                break
            }
            let response = await http.post(
                '',
                {
                    action: 'convertMoveFiles',
                    countPage: countPage.value,
                },
                {
                    // signal: abortController.signal,
                }
            )

            if (!(response?.success ?? false)) {
                throw new Error(response?.msg ?? 'Unknown error')
            }

            cntMoveConvertedFiles.value += response?.body?.cntMovedFiles ?? 0
            progress.value = Math.round((100 / steps) * i)
            console.log('progress.value', progress.value)

            let remainderFiles = cntPhysicalFiles.value - cntMoveConvertedFiles.value
            step2ProgressText.value = `${loc.value.step2ProgressTextReminder}: ${remainderFiles}`

            if (response?.body?.errorFiles ?? null) {
                for (let indx in response?.body?.errorFiles) {
                    errFiles.value.push(response.body.errorFiles[indx])
                }
            }

        }

        if (step2ShowProgress.value && !stopped.value) {
            moveSuccessfulStep2.value = true
        }

        // nextTick(() => {
        //     progressAnalysisPhysicalFiles()
        // })

        // loaded.value = true
    } catch (e: any) {
        console.error(e)
        error.value = e.message
    } finally {
        BX.closeWait()
        currentDirSize.value = '? Mb'
        currentCountFiles.value = '?'
        showProgress.value = false
        step2ShowProgress.value = false
    }
}

const errFilesString = computed(() => {
    return errFiles.value?.join('<br>') ?? '';
})

const stopProgress = () => {
    showProgress.value = false
    step2ShowProgress.value = false
    stopped.value = true
    // abortController.abort()
}

onMounted(() => {
    loc.value = Object.assign(
        {},
        loc.value,
        window.d2fLocalMessages
    )
    step2ProgressText.value = loc.value.step2ProgressText
    load()
})
</script>
