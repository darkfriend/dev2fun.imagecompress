<script setup lang="ts">
import {defineProps} from 'vue'

const COLOR_GRAY = 'gray'
const COLOR_RED = 'red'
const COLOR_GREEN = 'green'

defineProps({
    style: {
        type: String || Object,
        default: {
            position: 'relative',
            top: '-15px',
        }
    },
    color: {
        type: String,
        default: '',
    }
})

</script>

<template>
    <div
        class="adm-info-message-wrap"
        :class="{
            'adm-info-message-gray': color === COLOR_GRAY,
            'adm-info-message-red': color === COLOR_RED,
            'adm-info-message-green': color === COLOR_GREEN,
        }"
        :style="style"
    >
        <div class="adm-info-message">
            <slot />
        </div>
    </div>
</template>

<style scoped lang="scss">

</style>
