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
                <label for="translation" class="block text-base font-medium text-gray-700 mb-2">Translation</label>
                <input
                    id="translation"
                    type="text"
                    v-model="wordData.translation"
                    class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                    placeholder="Enter the translation"
                />
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
            <InertiaLink href="/" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                &larr; Back to Words
            </InertiaLink>
        </div>

        <div v-if="showTagModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Select Tags</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="tag in props.tags" :key="tag.id" class="flex items-center gap-2">
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
    // Reusables
    import { useAxiosRequest } from '../Reusables/AxiosRequest'

    const { axiosRequest } = useAxiosRequest()

    // Props
    const props = defineProps({
        word: {type: Object, default: {}},
        tags: {type: Array, default: {}},
    })


    const wordData = reactive({
        word: '',
        translation: '',
        description: '',
        tags: []
    })

    const selectedModalTagIds = ref([])
    const wordDataInitialized = ref(false)
    watch(() => props.word, (newValue, oldValue) => {
            if(props.word && !wordDataInitialized.value){
                initWordData()
                wordDataInitialized.value = true

                selectedModalTagIds.value = getSelectedTagIds()
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

    function saveButtonClicked(){
        let endpoint = mode.value == 'create' ? '/api/words/create' : '/api/words/update/' + props.word.id
        let method = mode.value == 'create' ? 'POST' : 'PUT'
        let apiData = wordData

        axiosRequest(endpoint, apiData, method, true).then((response) => {
            if(response.data.status == 'success'){
                router.visit('/')
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
                router.visit('/')
            } else{
                alert(response.msg)
            }
        })
    }
</script>


