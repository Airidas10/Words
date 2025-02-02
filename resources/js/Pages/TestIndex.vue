<template>
    <div class="max-w-4xl mx-auto space-y-4 px-4">
        <div v-for="(item, index) in testData" :key="index" class="flex items-center justify-between">
            <label :for="'question-' + index" class="text-base font-medium text-gray-700 w-1/3">{{ item.question }}</label>
            <input 
                :id="'question-' + index"
                v-model="item.answer"
                type="text"
                class="w-2/3 p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                placeholder="Your answer"
            />
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="submitForm" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Submit
            </button>
        </div>
    </div>
</template>


<script>
    export default{
        name: 'test-index'
    }
</script>

<script setup>
    // Vue stuff
    import { ref, watch } from 'vue'
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

    function submitForm(){
        let endpoint = '/api/tests/submit'
        let method = 'POST'
        let apiData = {id: props.testId, data: testData.value}

        axiosRequest(endpoint, apiData, method).then((response) => {
            if(response.data.status == 'success'){
                console.log("response", response)
            } else{
                alert(response.data.msg)
            }
        })
    }
</script>
