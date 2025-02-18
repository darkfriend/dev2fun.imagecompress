<script setup lang="ts">
import {defineProps} from 'vue'

defineProps({
    title: {
        type: String,
        default: 'Заголовок',
    },
    progressText: {
        type: String,
        default: 'Прогрес темплейт',
    },
    progress: {
        type: Number,
        default: 0,
    },
})


</script>

<template>
    <div class="adm-info-message-wrap adm-info-message-gray">
        <div class="adm-info-message">
            <div class="adm-info-message-title">
                {{ title }}
            </div>
            <div class="adm-progress-bar-outer" style="width: 500px;">
                <div
                    class="adm-progress-bar-inner"
                    :style="{
                        width: `${progress}%`
                    }"
                >
                    <div class="adm-progress-bar-inner-text" style="width: 500px;">
                        <span>{{ progressText }}</span>
                    </div>
                </div>
                <span>{{ progressText }}</span>
            </div>
            <div class="adm-info-message-buttons"></div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
