<template>
    <div class="bg-white shadow-md rounded-lg p-4 h-full relative min-w-[120px]">
        <InertiaLink :href="`/words/edit/${word.id}`" class="absolute top-2 right-2 text-sm text-blue-600 hover:underline flex items-center" @click.prevent.stop>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path d="M17.414 2.586a2 2 0 00-2.828 0L6 11.172V14h2.828l8.586-8.586a2 2 0 000-2.828zM7 12v-1.414l8.586-8.586a1 1 0 011.414 1.414L8.414 12H7z"/>
                <path fill-rule="evenodd" d="M4 15a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd"/>
            </svg>
            Edit
        </InertiaLink>

        <h2 class="text-xl font-semibold text-gray-800">{{ word.word }}</h2>
        <p class="text-gray-600">{{ showTranslation ? word.translation : '*****' }}</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <span  v-for="tag in word.tags"  @click.prevent.stop="handleTagClick(tag)" class="bg-blue-100 text-blue-600 px-2 py-1 rounded-full text-sm">
                {{ tag.tag }}
            </span>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'word-card'
    }
</script>

<script setup>
    // Vue stuff
    import { computed } from 'vue' 
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import { useStore } from 'vuex'

    const store = useStore()

    const props = defineProps({
        word: {type: Object},
    })

    const emits = defineEmits(['tagClick'])

    const showTranslation = computed(() => store.state.showTranslation)

    function handleTagClick(tag){
        let data = {tag: tag}
        emits('tagClick', data)
    }

</script>
