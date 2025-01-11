<template>
    <div class="word-page">
        <h1 class="text-3xl font-semibold">{{ word.word }}</h1>
        <p class="text-gray-600 mt-4">{{ word.translation }}</p>

        <div v-if="word.tags.length" class="mt-6">
            <h3 class="text-lg font-medium">Tags:</h3>
            <ul class="flex flex-wrap gap-2 mt-2">
                <li v-for="tag in word.tags" :key="tag.id" class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm">
                    {{ tag.tag }}
                </li>
            </ul>
        </div>

        <div v-else class="mt-6">
            <p>No tags available.</p>
        </div>
    </div>
</template>

<script setup>
    defineProps({
      word: Object
    })
</script>

<style scoped>
    .word-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
</style>
