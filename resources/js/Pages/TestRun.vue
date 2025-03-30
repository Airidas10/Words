<template>
    <div class="max-w-4xl mx-auto space-y-4 px-4">
        <div v-for="(item, index) in testData" :key="index" class="flex items-center justify-between">
            <label :for="'question-' + index" class="text-base font-medium text-gray-700 w-1/3">
                {{ item.question }}
                <span v-if="item.help" class="ml-2 text-blue-500 cursor-pointer" @click.prevent.stop="togglePopover(item)">
                     ℹ️ 
                </span>
            </label>

            <div @click.prevent.stop v-if="item.showPopover" class="absolute bg-white border shadow-lg p-2 rounded-lg mt-1 z-10 w-48">
                <p class="text-sm text-gray-700">{{ item.help }}</p>
                <button @click.prevent.stop="togglePopover(item)" class="text-red-500 text-xs mt-2 underline">
                    Close
                </button>
            </div>

            <div class="w-2/3 flex items-center space-x-3">
                <input
                    :id="'question-' + index"
                    autocomplete="off"
                    v-model="item.answer"
                    type="text"
                    :class="{
                        'flex-1 p-3 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400': true,
                    }"
                    :placeholder="getPlaceholder(item)"
                />
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="handleButtonClick" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Submit
            </button>
        </div>
    </div>
</template>

<script>
    export default{
        name: 'test-run'
    }
</script>

<script setup>
    // Vue stuff
    import { ref, computed, watch } from 'vue'
    // Libraries
    import { Link as InertiaLink, router} from '@inertiajs/vue3'
    // Reusables
    import { useAxiosRequest } from '../Reusables/AxiosRequest'

    const { axiosRequest } = useAxiosRequest()

    // Props
    const props = defineProps({
        testId: {type: Number},
        testJson : {type: String},
    })

    const testData = ref({})
    watch(() => props.testJson, (newValue, oldValue) => {
            testData.value = JSON.parse(props.testJson)
        }, {deep: true, immediate: true}
    )

    function getPlaceholder(item){
        return item?.type == 't' ? "Your answer (IT)" : "Your answer (LT)"
    }

    function togglePopover(item){
        item.showPopover = !item.showPopover
    }

    function handleButtonClick(){
        if(confirm("Are you sure you are ready to submit your answers?") == true) {
            submitForm()
        }
    }

    function submitForm(){
        let endpoint = '/api/tests/submit'
        let method = 'POST'
        let apiData = {testId: props.testId, testData: testData.value}

        axiosRequest(endpoint, apiData, method).then((response) => {
            if(response.status == 'success'){
                router.visit('/runs/' + response.data.id)
            } else{
                alert(response.msg)
            }
        })
    }
</script>
