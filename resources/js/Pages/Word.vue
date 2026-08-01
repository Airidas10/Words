<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 bg-white rounded-lg shadow-md flex flex-col items-center">
        <div v-if="isRandomPage" class="w-full mb-6">
            <label for="random-pool-select" class="block text-sm font-medium text-gray-700 mb-2 text-center">Word pool</label>
            <select
                id="random-pool-select"
                data-testid="random-pool-select"
                class="block w-full max-w-md mx-auto rounded-md border border-blue-200 bg-white px-3 py-2 text-gray-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :value="poolSelectValue"
                @change="onPoolChange"
            >
                <option value="all">Full Random</option>
                <option v-if="user" value="struggles">My Struggles</option>
                <option
                    v-for="tag in tags"
                    :key="tag.id"
                    :value="`tag:${tag.id}`"
                >
                    {{ tag.tag }}
                </option>
            </select>
        </div>

        <template v-if="word">
            <div class="flex items-center justify-center w-full mb-6">
                <div class="flex flex-col sm:flex-row items-center justify-center w-full gap-2">
                    <h1 class="text-3xl font-semibold text-gray-800 text-center break-words">{{ word.word }}</h1>
                    <StruggleToggle
                        v-if="user"
                        :word-id="word.id"
                        :in-struggles="isInStruggles"
                    />
                </div>
            </div>
            <span class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm mb-4" @click="toggleTranslationVisibility">
                {{ showTranslation ? 'Hide' : 'Show' }} Translation
            </span>

            <h1 class="text-xl font-semibold text-gray-800">{{ translationHeader }}</h1>
            <p v-for="translation in word.translations" :key="translation.id" class="text-lg text-gray-600 flex items-center justify-center">
                <span>{{ showTranslation ? translation.translation : '*****' }}</span>
            </p>

            <div v-if="word.tags?.length && showTags" class="mt-6 w-full">
                <h3 class="text-lg font-medium text-gray-800 text-center">Tags:</h3>
                <ul class="flex flex-wrap justify-center gap-2 mt-2">
                    <li v-for="tag in word.tags" :key="tag.id" class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm">
                        {{ tag.tag }}
                    </li>
                </ul>
            </div>

            <div v-else-if="word.tags?.length == 0" class="mt-6 w-full text-center">
                <p class="text-gray-600">No tags available.</p>
            </div>
            <div v-else-if="!showTags" class="mt-6 w-full text-center">
                <p class="text-gray-600">Tags are hidden.</p> <span class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm mb-4" @click="toggleTags">{{ showTags ? 'Hide' : 'Show' }}</span>
            </div>

            <div v-if="word.description && showDescription" class="mt-6 w-full text-center p-3 border border-gray-100 rounded-lg bg-gray-50 shadow-sm">
                <h3 class="text-lg font-medium text-gray-800">Description:</h3>
                <p class="text-gray-700 whitespace-pre-line">{{ word.description }}</p>
            </div>

            <div v-else-if="word.description && !showDescription" class="mt-6 w-full text-center">
                <p class="text-gray-600">Description is hidden.</p> <span class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm mb-4" @click="toggleDescription">{{ showDescription ? 'Hide' : 'Show' }}</span>
            </div>

            <WordOverallStats
                v-if="!isRandomPage || showStats"
                :stats="wordStats"
                :word-id="word.id"
                variant="detailed"
            />
            <div v-else-if="user" class="mt-6 w-full text-center">
                <p class="text-gray-600">Stats are hidden.</p>
                <span class="text-blue-600 hover:text-blue-800 ml-2 cursor-pointer text-sm mb-4" @click="toggleStats">{{ showStats ? 'Hide' : 'Show' }}</span>
            </div>
        </template>

        <div v-else class="text-center py-8">
            <p class="text-gray-600 text-lg">No words in this pool.</p>
        </div>

        <div class="mt-6 w-full text-center">
            <div v-if="isRandomPage" class="sm:ml-4 mt-2 sm:mt-0">
                <InertiaLink
                    :href="nextRandomHref"
                    class="bg-blue-800 hover:bg-blue-900 text-white text-lg font-bold px-8 py-3 rounded-lg shadow-lg transition-colors duration-150"
                    @click="nextWordClicked"
                >
                    Next
                </InertiaLink>
            </div>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'word-page'
    }
</script>

<script setup>
    import { computed, ref, watch } from 'vue'
    import { Link as InertiaLink, router, usePage } from '@inertiajs/vue3'
    import { useStore } from 'vuex'
    import WordOverallStats from '../Components/WordOverallStats.vue'
    import StruggleToggle from '../Components/StruggleToggle.vue'

    const store = useStore()
    const page = usePage()
    const url = computed(() => page.url)

    const props = defineProps({
        word: { type: Object, default: null },
        wordStats: { type: Object, default: null },
        randomPool: { type: String, default: 'all' },
        tagId: { type: [Number, String], default: null },
        tags: { type: Array, default: () => [] },
        struggleWordIds: { type: Array, default: null },
    })

    const user = computed(() => store.state.user)

    const isRandomPage = computed(() => url.value === '/random' || url.value.startsWith('/random?'))

    const isInStruggles = computed(() => {
        if (!props.word || !props.struggleWordIds) {
            return false
        }
        return props.struggleWordIds.includes(props.word.id)
    })

    const poolSelectValue = computed(() => {
        if (props.randomPool === 'struggles') {
            return 'struggles'
        }
        if (props.randomPool === 'tag' && props.tagId != null) {
            return `tag:${props.tagId}`
        }
        return 'all'
    })

    const nextRandomHref = computed(() => {
        if (props.randomPool === 'struggles') {
            return '/random?pool=struggles'
        }
        if (props.randomPool === 'tag' && props.tagId != null) {
            return `/random?pool=tag&tag_id=${props.tagId}`
        }
        return '/random'
    })

    function onPoolChange(event) {
        const value = event.target.value
        let href = '/random'

        if (value === 'struggles') {
            href = '/random?pool=struggles'
        } else if (value.startsWith('tag:')) {
            const tagId = value.slice('tag:'.length)
            href = `/random?pool=tag&tag_id=${tagId}`
        }

        router.visit(href)
    }

    const showTranslation = computed(() => store.state.showTranslation)
    const showTags = ref(true)
    const showDescription = ref(true)
    const showStats = ref(true)

    function toggleDescription(){
        showDescription.value = ! showDescription.value
    }

    function toggleTags(){
        showTags.value = ! showTags.value
    }

    function toggleStats(){
        showStats.value = ! showStats.value
    }

    watch(showTranslation, () => {
            if(showTranslation.value){
                showTags.value = true
                showDescription.value = true
                showStats.value = true
            } else{
                showTags.value = false
                showDescription.value = false
                showStats.value = false
            }
        }, {immediate: true }
    )

    function toggleTranslationVisibility(){
        store.commit('setShowTranslation', !showTranslation.value)
    }

    const translationHeader = computed(() => {
        return props.word?.translations?.length > 1 ? 'Translations:' : 'Translation:'
    })

    function nextWordClicked(){
        store.commit('setShowTranslation', false)
    }

</script>

<style scoped>
    .word-page {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
</style>
