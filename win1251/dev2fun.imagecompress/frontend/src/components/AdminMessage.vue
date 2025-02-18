<script setup lang="ts">
import {defineProps, computed} from 'vue'

const TYPE_ERROR = 'ERROR'
const TYPE_OK = 'OK'

const props = defineProps({
    title: {
        type: String,
        default: 'Заголовок',
    },
    type: {
        type: String,
        default: TYPE_ERROR,
    }
})

const admClassMessage = computed(() => {
    return props.type === TYPE_OK ? 'adm-info-message-green' : 'adm-info-message-red'
})

</script>

<template>
    <div
        class="adm-info-message-wrap"
        :class="admClassMessage"
    >
        <div class="adm-info-message">
            <div class="adm-info-message-title">
                {{ title }}
            </div>

            <div class="adm-info-message-icon"></div>
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
