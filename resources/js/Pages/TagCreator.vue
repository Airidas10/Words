<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create New Tag</h1>
        <form>
            <div class="mb-4">
                <label for="tag" class="block text-sm font-medium text-gray-700">Tag</label>
                <input
                  id="tag"
                  type="text"
                  v-model="tagData.tag"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter the tag"
                />
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" @click.prevent.stop="saveButtonClicked">
                Save
            </button>

            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" @click.prevent.stop="deleteButtonClicked">
                Delete
            </button>
        </form>

        <div class="mt-6">
            <InertiaLink href="/tags" class="text-blue-600 hover:text-blue-800 font-medium">
                &larr; Back to Tags
            </InertiaLink>
        </div>
    </div>
</template>


<script>
    export default{
        name: 'tag-creator'
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
        tag: {type: Object, default: {}},
    })

    const tagData = reactive({
        tag: '',
    })

    const mode = computed(() => { 
        return props.tag === null ? 'create' : 'edit'
    }) 

    const tagDataInitialized = ref(false)
    watch(() => props.tag, (newValue, oldValue) => {
            if(props.tag && !tagDataInitialized.value){
                initTagData()
                tagDataInitialized.value = true
            }
        }, {deep: true, immediate: true}
    )


    function initTagData(){
        tagData.tag = props.tag.tag
    }

    function saveButtonClicked(){
        let endpoint = mode.value == 'create' ? '/api/tags/create' : '/api/tags/update/' + props.tag.id
        let method = mode.value == 'create' ? 'POST' : 'PUT'
        let apiData = tagData

        axiosRequest(endpoint, apiData, method).then((response) => {
            console.log("response", response)
            if(response.status == 'success'){
                router.visit('/tags')
            } else{
                alert(response.msg)
            }
        })
    }

    function deleteButtonClicked(){
        let endpoint = '/api/tags/destroy/' + props.tag.id
        let method = 'DELETE'
        let apiData = {}

        axiosRequest(endpoint, apiData, method).then((response) => {
            console.log("response", response)
            if(response.status == 'success'){
                router.visit('/tags')
            } else{
                alert(response.msg)
            }
        })
    }
</script>


