<template>
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-lg mt-8">
        <h1 class="text-3xl font-semibold text-gray-800 mb-8 text-center">{{ mode == 'create' ? 'Create New Tag' : 'Edit Tag' }}</h1>
        <form>
            <div
                v-if="formError()"
                class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-center text-red-700"
                data-testid="form-error"
            >
                {{ formError() }}
            </div>

            <div class="mb-6">
                <label for="tag" class="block text-base font-medium text-gray-700 mb-2">Tag</label>
                <input
                    id="tag"
                    type="text"
                    v-model="tagData.tag"
                    :class="[
                        'w-full p-3 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400',
                        fieldError('tag') ? 'border-red-500' : 'border-gray-300',
                    ]"
                    placeholder="Enter a tag name"
                    @input="clearErrors"
                />
                <p v-if="fieldError('tag')" class="mt-2 text-sm text-red-600" data-testid="tag-error">
                    {{ fieldError('tag') }}
                </p>
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
    import { useFormErrors } from '../Reusables/FormErrors'

    const { axiosRequest } = useAxiosRequest()
    const { clearErrors, setErrors, fieldError, formError, applyErrorResponse } = useFormErrors()

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

    function validateTagData(){
        const nextErrors = {}

        if(! String(tagData.tag ?? '').trim()){
            nextErrors.tag = ['The tag field is required.']
        }

        return nextErrors
    }

    function saveButtonClicked(){
        let endpoint = mode.value == 'create' ? '/api/tags/create' : '/api/tags/update/' + props.tag.id
        let method = mode.value == 'create' ? 'POST' : 'PUT'
        let apiData = tagData

        clearErrors()

        const clientErrors = validateTagData()
        if(Object.keys(clientErrors).length > 0){
            setErrors(clientErrors)
            return
        }

        axiosRequest(endpoint, apiData, method, true).then((response) => {
            if(response.data.status == 'success'){
                router.visit('/tags')
            } else{
                applyErrorResponse({ data: response.data })
            }
        }).catch((errorResponse) => {
            applyErrorResponse(errorResponse)
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

        clearErrors()

        axiosRequest(endpoint, apiData, method, true).then((response) => {
            if(response.data.status == 'success'){
                router.visit('/tags')
            } else{
                applyErrorResponse({ data: response.data })
            }
        }).catch((errorResponse) => {
            applyErrorResponse(errorResponse)
        })
    }
</script>
