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

        <div class="mt-6">
            <InertiaLink href="/" class="text-blue-600 hover:text-blue-800 font-medium">
                &larr; Back to Words
            </InertiaLink>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'word-page'
    }
</script>

<script setup>
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'

    // Props
    const props = defineProps({
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
