<template>
    <div class="word-page">

        <div class="flex items-center">
            <h1 class="text-3xl font-semibold inline">{{ word.word }}</h1>
            <InertiaLink v-if="isRandomPage" href="/random" class="text-blue-500 text-sm ml-2 hover:underline">Regenerate</InertiaLink>
        </div>
        
        <p class="text-gray-600 mt-4 flex items-center">
            <span>{{ showTranslation ? word.translation : '*****' }}</span>
            <span class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm" @click="toggleTranslationVisibility">
                {{ showTranslation ? 'Hide' : 'Show' }} Translation
            </span>
        </p>

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
    // Vue stuff
    import { computed } from 'vue' 
    // Libraries
    import { Link as InertiaLink, usePage } from '@inertiajs/vue3'
    import { useStore } from 'vuex'

    const store = useStore()
    const { url } = usePage()

    // Props
    const props = defineProps({
        word: Object
    })

    const isRandomPage = computed(() => {
        return (url === '/random') ? true : false
    })

    const showTranslation = computed(() => store.state.showTranslation)

    function toggleTranslationVisibility(){
        store.commit('setShowTranslation', !showTranslation.value)
    }

</script>

<style scoped>
    .word-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
</style>
