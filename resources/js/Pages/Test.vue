<template>
    <div class="max-w-4xl mx-auto space-y-4 px-4">
        <div class="text-center mb-6">
            <h1 class="text-4xl font-bold text-gray-800">Your score is: {{ test.score }} / {{ test.number_of_questions }}</h1>
        </div>

        <div class="max-w-4xl mx-auto px-4">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-300 px-4 py-2 text-left">Question</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Your Answer</th>
                        <th class="border border-gray-300 px-4 py-2 text-left">Correct Answer</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">Result</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in testData" :key="index" class="border-t">
                        <td class="border border-gray-300 px-4 py-2">{{ item.question }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ item.answer }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ item.correctAnswer }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <span v-if="item.correct === true" class="text-green-500 text-xl">✔</span>
                            <span v-else-if="item.correct === false" class="text-red-500 text-xl">✘</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-center">
            <InertiaLink href="/daily-dose" class="text-blue-500 text-sm ml-2 hover:underline">Click here to start a new run</InertiaLink>
        </div>
    </div>
</template>
    

<script>
    export default{
        name: 'test'
    }
</script>

<script setup>
    // Vue stuff
    import { ref, computed } from 'vue'
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'

    // Props
    const props = defineProps({
        test: {type: Object},
    })

    const testData = computed(() => {
        return props.test ? JSON.parse(props.test.questions_and_answers) : null
    })
</script>
