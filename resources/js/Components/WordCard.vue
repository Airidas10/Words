<template>
    <div class="bg-white shadow-md rounded-lg p-4 h-full">
        <h2 class="text-xl font-semibold text-gray-800">{{ word.word }}</h2>
        <p class="text-gray-600">{{ word.translation }}</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <span v-for="tag in word.tags" class="bg-blue-100 text-blue-600 px-2 py-1 rounded-full text-sm">{{ tag.tag }}</span>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'word-card'
    }
</script>

<script setup>
    const props = defineProps({
        word: {type: Object},
    })
    
</script>
