<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create New Word</h1>
        <form>
            <div class="mb-4">
                <label for="word" class="block text-sm font-medium text-gray-700">Word</label>
                <input
                  id="word"
                  type="text"
                  v-model="wordData.word"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter the word"
                />
            </div>
            <div class="mb-4">
                <label for="translation" class="block text-sm font-medium text-gray-700">Translation</label>
                <input
                  id="translation"
                  type="text"
                  v-model="wordData.translation"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter the translation"
                />
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  id="description"
                  v-model="wordData.description"
                  rows="4"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter a description"
                ></textarea>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" @click.prevent.stop="saveButtonClicked">
                Save
            </button>
        </form>

        <div class="mt-6">
            <InertiaLink href="/" class="text-blue-600 hover:text-blue-800 font-medium">
                &larr; Back to Words
            </InertiaLink>
        </div>
    </div>
</template>


<script>
    export default{
        name: 'word-creator'
    }
</script>

<script setup>
    // Vue stuff
    import { ref, reactive, computed, watch } from 'vue'
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'
    // Reusables
    import { useAxiosRequest } from '../Reusables/AxiosRequest'

    const { axiosRequest } = useAxiosRequest()

    // Props
    const props = defineProps({
        word: {type: Object, default: {}},
    })

    const wordData = reactive({
        word: '',
        translation: '',
        description: '',
    })

    const mode = computed(() => { 
        return props.word === null ? 'create' : 'edit'
    }) 

    const wordDataInitialized = ref(false)
    watch(() => props.word, (newValue, oldValue) => {
            if(props.word && !wordDataInitialized.value){
                initWordData()
                wordDataInitialized.value = true
            }
        }, {deep: true, immediate: true}
    )


    function initWordData(){
        wordData.word = props.word.word
        wordData.translation = props.word.translation
        wordData.description = props.word.description
    }

    function saveButtonClicked(){
        console.log("saveButtonClicked")
        let endpoint = 'TODO'
        let method = 'POST'

        axiosRequest(endpoint, apiData, method).then((response) => {
            console.log("response", response)
        })
    }
</script>


