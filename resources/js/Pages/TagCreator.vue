<template>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-8">
        <h1 class="text-3xl font-semibold text-gray-800 mb-8 text-center">{{ mode == 'create' ? 'Create New Tag' : 'Edit Tag' }}</h1>
        <form>
            <div class="mb-6">
                <label for="tag" class="block text-base font-medium text-gray-700 mb-2">Tag</label>
                <input
                    id="tag"
                    type="text"
                    v-model="tagData.tag"
                    class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                    placeholder="Enter a tag name"
                />
            </div>

            <div class="flex items-center justify-between gap-4">
                <button
                v-if="mode != 'create'"
                type="submit"
                class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                @click.prevent.stop="deleteButtonClicked"
                >
                Delete
            </button>
            <button
                type="submit"
                class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                @click.prevent.stop="saveButtonClicked"
            >
                Save
            </button>
        </div>
        </form>

        <div class="mt-8 text-center">
            <InertiaLink href="/tags" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
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
        if(confirm("Are you sure you want to delete this tag?") == true) {
            handleDeletion()
        }
    }

    function handleDeletion(){
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


