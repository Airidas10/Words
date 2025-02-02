<template>
    <div class="max-w-4xl mx-auto space-y-4 px-4">
        <div v-if="answersWereSubmitted" class="text-center mb-6">
            <h1 class="text-4xl font-bold text-gray-800">Your score is: {{ score }} / {{ questionCount }}</h1>
        </div>
        <div v-for="(item, index) in testData" :key="index" class="flex items-center justify-between">
            <label :for="'question-' + index" class="text-base font-medium text-gray-700 w-1/3">{{ item.question }}</label>
            <div class="w-2/3 flex items-center space-x-3">
                <input v-if="!displayAnswers"
                    :id="'question-' + index"
                    v-model="item.answer"
                    type="text"
                    :class="{
                        'flex-1 p-3 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400': true,
                        'border-green-500 focus:border-green-500': item.correct === true,
                        'border-red-500 focus:border-red-500': item.correct === false
                    }"
                    placeholder="Your answer"
                />

                <span v-else>{{ item.correctAnswer }}</span>

                <span v-if="item.correct === true" class="text-green-500 text-xl">✔</span>
                <span v-else-if="item.correct === false" class="text-red-500 text-xl">✘</span>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button @click="handleButtonClick" class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ answersWereSubmitted ? 'Show Correct Answers' : 'Submit'}}
            </button>
        </div>

        <div class="mt-6 flex justify-center">
            <InertiaLink v-if="answersWereSubmitted" href="/daily-dose" class="text-blue-500 text-sm ml-2 hover:underline">Try again</InertiaLink>
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
    import { ref, computed, watch } from 'vue'
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'
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

    const score = ref(null)

    const answersWereSubmitted = computed(() => {
        return (score.value !== null) ? true : false
    })

    const questionCount = computed(() => {
        return Object.keys(testData.value).length
    })

    const displayAnswers = ref(false)
    function handleButtonClick(){
        if(answersWereSubmitted.value){
            displayAnswers.value = true
        } else{
            submitForm()
        }
    }

    function submitForm(){
        let endpoint = '/api/tests/submit'
        let method = 'POST'
        let apiData = {testId: props.testId, testData: testData.value}

        axiosRequest(endpoint, apiData, method).then((response) => {
            if(response.status == 'success'){
                // console.log("response", response)
                let answers = response.data.answers
                for(let i = 0; i < answers.length; i++){
                    let answer = answers[i]
                    let testItem = getTestObject(answer.id)
                    if(testItem){
                        testItem.correct = answer.correct
                        testItem.correctAnswer = answer.correctAnswer
                    }
                }
                score.value = response.data.score
            } else{
                alert(response.msg)
            }
        })
    }

    function getTestObject(id){
        let objFound = null
        for(let key in testData.value){
            let object = testData.value[key]
            if(object.id == id){
                objFound = object
                break
            }
        }

        return objFound
    }
</script>
