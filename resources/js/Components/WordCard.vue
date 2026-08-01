<template>
    <div class="bg-white shadow-md rounded-lg p-4 h-full relative min-w-[120px] flex flex-col">
        <div v-if="user" class="absolute top-2 right-2 flex items-center gap-1 z-10">
            <StruggleToggle
                :word-id="word.id"
                :in-struggles="inStrugglesLocal"
                @changed="handleStruggleChanged"
            />
            <InertiaLink :href="`/words/edit/${word.id}`" class="text-sm text-blue-600 hover:underline flex items-center" @click.prevent.stop="handleEditClick">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L6 11.172V14h2.828l8.586-8.586a2 2 0 000-2.828zM7 12v-1.414l8.586-8.586a1 1 0 011.414 1.414L8.414 12H7z"/>
                    <path fill-rule="evenodd" d="M4 15a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd"/>
                </svg>
                Edit
            </InertiaLink>
        </div>

        <h2 class="text-xl font-semibold text-gray-800">{{ word.word }}</h2>
        <div class="flex-grow">
            <p v-for="translation in word.translations" :key="translation.id" class="text-gray-600">{{ showTranslation ? translation.translation : '*****' }}</p>
        </div>

        <div v-if="word.description" class="relative flex-grow">
            <button @click.prevent.stop="togglePopover" class="text-blue-500 text-sm underline mt-1">
                ⚠️
            </button>

            <div v-if="isPopoverOpen" @click.prevent.stop class="absolute left-0 mt-2 w-64 bg-white shadow-lg rounded-lg p-4 border border-gray-200 z-50">
                <p class="text-gray-700 text-sm">
                    {{ word.description }}
                </p>
                <button @click.prevent.stop="togglePopover" class="text-red-500 text-xs mt-2 underline">
                    Close
                </button>
            </div>
        </div>

        <div class="mt-4 flex items-end gap-2">
            <div class="flex flex-wrap gap-2 flex-1 min-w-0">
                <span
                    v-for="tag in word.tags"
                    :key="tag.id"
                    @click.prevent.stop="handleTagClick(tag)"
                    class="bg-blue-100 text-blue-600 px-2 py-1 rounded-full text-sm"
                >
                    {{ tag.tag }}
                </span>
            </div>
            <WordOverallStats :stats="stats" :word-id="word.id" />
        </div>
    </div>
</template>

<script>
    export default{
        name: 'word-card'
    }
</script>

<script setup>
    import { ref, computed, watch } from 'vue'
    import { Link as InertiaLink } from '@inertiajs/vue3'
    import { useStore } from 'vuex'
    import WordOverallStats from './WordOverallStats.vue'
    import StruggleToggle from './StruggleToggle.vue'

    const store = useStore()

    const props = defineProps({
        word: { type: Object },
        stats: { type: Object, default: null },
        inStruggles: { type: Boolean, default: false },
    })

    const emits = defineEmits(['tagClick', 'struggleChanged'])

    const showTranslation = computed(() => store.state.showTranslation)
    const user = computed(() => store.state.user)
    const inStrugglesLocal = ref(props.inStruggles)

    watch(() => props.inStruggles, (value) => {
        inStrugglesLocal.value = value
    })

    const isPopoverOpen = ref(false)
    function togglePopover(){
        isPopoverOpen.value = !isPopoverOpen.value
    }

    function handleTagClick(tag){
        let data = {tag: tag}
        emits('tagClick', data)
    }

    function handleStruggleChanged(payload) {
        inStrugglesLocal.value = payload.inStruggles
        emits('struggleChanged', payload)
    }

    function handleEditClick(){
        let currentUrl = window.location.href
        if(currentUrl){
            store.commit("saveWordEditUrl", currentUrl)
        }
    }

</script>
