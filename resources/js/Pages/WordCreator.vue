<template>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-8">
        <h1 class="text-3xl font-semibold text-gray-800 mb-8 text-center">{{ mode == 'create' ? 'Create New Word' : 'Edit Word' }}</h1>
        <form>
            <div class="mb-6">
                <label for="word" class="block text-base font-medium text-gray-700 mb-2">Word</label>
                <input
                    id="word"
                    type="text"
                    v-model="wordData.word"
                    class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                    placeholder="Enter the word"
                />
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <label for="translation" class="block text-base font-medium text-gray-700 mb-2">Translations</label> 
                    <span class="text-blue-600 text-sm cursor-pointer hover:underline" @click="addTranslation">Add more</span>
                </div>

                <div v-for="(translation, index) in translations" :key="translation.id" class="relative mb-2">
                    <div class="relative">
                        <input
                            :id="'translation-' + translation.id"
                            type="text"
                            v-model="translation.translation"
                            class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                            placeholder="Enter the translation"
                        />

                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-2">
                            <span v-if="!translationHasHelpInput(translation)" class="text-gray-400 hover:text-blue-500 cursor-pointer text-lg" @click="addHelpInput(translation.id)" title="Add translation help">
                                ❓
                            </span>

                            <span v-if="translations.length > 1" class="text-gray-400 hover:text-red-500 cursor-pointer text-lg" @click="removeTranslation(translation.id)">
                                &times;
                            </span>
                        </div>
                    </div>

                    <div v-if="translationHasHelpInput(translation)" class="relative mt-2 ml-5">
                        <input
                            type="text"
                            v-model="translation.test_help"
                            class="w-full mt-2 ml-5 p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                            placeholder="Enter help text..."
                        />
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-red-500 cursor-pointer text-lg" @click="removeHelpInput(translation.id)" title="Remove translation help">
                            &times;
                        </span>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label for="description" class="block text-base font-medium text-gray-700 mb-2">Description</label>
                <textarea
                    id="description"
                    v-model="wordData.description"
                    rows="4"
                    class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                    placeholder="Enter a description"
                ></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-base font-medium text-gray-700 mb-2">Tags</label>
                <div class="flex items-center gap-4">
                    <div class="flex flex-wrap gap-2">
                        <span v-for="tag in wordData.tags" :key="tag.id" class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full flex items-center gap-2">
                            {{ tag.tag }}
                        </span>
                        <span v-if="wordData.tags?.length == 0" class="text-gray-600 text-sm">No tags yet</span>
                    </div>
                    <button type="button" class="bg-gray-200 text-gray-600 p-2 rounded-full shadow hover:bg-gray-300 focus:outline-none focus:ring focus:ring-gray-400" @click="openTagModal">
                        +
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" @click.prevent.stop="saveButtonClicked">
                    Save
                </button>
                <button type="submit" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" @click.prevent.stop="deleteButtonClicked">
                    Delete
                </button>
            </div>
        </form>

        <div class="mt-8 text-center">
            <a class="text-blue-600 hover:text-blue-800 text-sm font-medium" href="#" onclick="history.back()">&larr; Go Back</a>
        </div>

        <div v-if="showTagModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Select Tags</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="tag in tags" :key="tag.id" class="flex items-center gap-2">
                        <input type="checkbox" :id="`tag-${tag.id}`" :value="tag.id" v-model="selectedModalTagIds" class="rounded text-blue-600 focus:ring-blue-500"/>
                        <label :for="`tag-${tag.id}`" class="text-gray-700">{{ tag.tag }}</label>
                    </div>
                </div>
                <div class="flex justify-end mt-6 gap-4">
                    <button type="button" class="bg-gray-200 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring focus:ring-gray-400" @click="closeTagModal">
                        Cancel
                    </button>
                    <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" @click="applyTags">
                        Add Tags
                    </button>
                </div>
            </div>
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
    import { Link as InertiaLink, router } from '@inertiajs/vue3'
    import { useStore } from 'vuex'
    // Reusables
    import { useAxiosRequest } from '../Reusables/AxiosRequest'

    const store = useStore()

    const { axiosRequest } = useAxiosRequest()

    // Props
    const props = defineProps({
        word: {type: Object, default: {}},
        tags: {type: Array, default: {}},
    })

    const wordData = reactive({
        word: '',
        description: '',
        tags: []
    })

    const nextTempTranslationId = computed(() => {
        return translations.value?.length + 1
    })
    const translations = ref([])

    function addTranslation(){
        let newTranslationObj = {id: 'temp-' + nextTempTranslationId.value, translation: null, test_help: null}
        translations.value.push(newTranslationObj)
    }

    function removeTranslation(translationId){
        let index = translations.value.findIndex(translationObj => translationObj.id == translationId)
        if(index != -1){
            translations.value.splice(index, 1)
        }
    }

    function translationHasHelpInput(translationObj){
        return (translationObj?.test_help === null) ? false : true
    }

    function addHelpInput(translationId){
        let translationObj = translations.value.find(translationObj => translationObj.id == translationId)
        if(translationObj){
            translationObj.test_help = ''
        }
    }

    function removeHelpInput(translationId){
        let translationObj = translations.value.find(translationObj => translationObj.id == translationId)
        if(translationObj){
            translationObj.test_help = null
        }
    }

    const selectedModalTagIds = ref([])
    const wordDataInitialized = ref(false)
    watch(() => props.word, (newValue, oldValue) => {
            if(props.word && !wordDataInitialized.value){
                initWordData()                
                wordDataInitialized.value = true

                selectedModalTagIds.value = getSelectedTagIds()
                translations.value = props.word.translations
            }

            if(translations.value.length == 0){
                addTranslation()
            }
        }, {deep: true, immediate: true}
    )

    function getSelectedTagIds(){
        let selectedTags = []
        if(wordData.tags?.length > 0){
            for(let i = 0; i < wordData.tags.length; i++){
                let tag = wordData.tags[i]
                selectedTags.push(tag.id)
            }
        }
        return selectedTags
    }

    function initWordData(){
        wordData.word = props.word.word
        wordData.translation = props.word.translation
        wordData.description = props.word.description
        wordData.tags = props.word.tags
    }


    function applyTags(){
        let tagObjects = []
        for(let i = 0; i < selectedModalTagIds.value?.length; i++){
            let tagId = selectedModalTagIds.value[i]
            let tagObj = props.tags.find(tag => tag.id == tagId)
            if(tagObj){
                tagObjects.push(tagObj)
            }
        }

        wordData.tags = tagObjects
        closeTagModal()
    }

    const showTagModal = ref(false)
    function closeTagModal(){
        showTagModal.value = false
    }

    function openTagModal(){
        showTagModal.value = true
    }

    const mode = computed(() => { 
        return props.word === null ? 'create' : 'edit'
    })

    const wordEditUrl = computed(() => {
        return store.state.wordEditUrl ? store.state.wordEditUrl : '/'
    })

    function saveButtonClicked(){
        let endpoint = mode.value == 'create' ? '/api/words/create' : '/api/words/update/' + props.word.id
        let method = mode.value == 'create' ? 'POST' : 'PUT'
        let apiData = wordData
        apiData.translations = translations.value

        axiosRequest(endpoint, apiData, method, true).then((response) => {
            if(response.data.status == 'success'){
                router.visit(wordEditUrl.value)
            } else{
                alert(response.data.msg)
            }
        })
    }

    function deleteButtonClicked(){
        let endpoint = '/api/words/destroy/' + props.word.id
        let method = 'DELETE'
        let apiData = {}

        axiosRequest(endpoint, apiData, method).then((response) => {
            console.log("response", response)
            if(response.status == 'success'){
                router.visit(wordEditUrl.value)
            } else{
                alert(response.msg)
            }
        })
    }
</script>


