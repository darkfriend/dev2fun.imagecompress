<template>
    <slot></slot>
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

// const ProgressBar = defineAsyncComponent(() => import('@/components/ProgressBar.vue'))
// const AdminMessage = defineAsyncComponent(() => import('@/components/AdminMessage.vue'))
// const AdminNote = defineAsyncComponent(() => import('@/components/AdminNote.vue'))

const MoverFiles = defineAsyncComponent(() => import('@/components/MoverFiles.vue'))

</script>
