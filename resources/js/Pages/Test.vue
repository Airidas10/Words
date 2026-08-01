<template>
    <div class="max-w-4xl mx-auto space-y-4 px-4">
        <div class="text-center mb-3 md:mb-6">
            <h1 class="text-2xl md:text-4xl font-bold text-gray-800">Your score is: {{ test.score }} / {{ test.number_of_questions }}</h1>
        </div>

        <div class="divide-y divide-gray-200 rounded-lg border border-gray-200 bg-white md:hidden">
            <div
                v-for="(item, index) in testData"
                :key="index"
                class="px-3 py-2"
            >
                <div class="flex items-start gap-1.5 text-sm">
                    <span v-if="item.correct === true" class="shrink-0 text-green-500 leading-5">✔</span>
                    <span v-else-if="item.correct === false" class="shrink-0 text-red-500 leading-5">✘</span>
                    <p class="min-w-0 flex-1 font-medium text-gray-800 break-words leading-5">{{ item.question }}</p>
                </div>
                <p class="mt-0.5 pl-5 text-xs text-gray-600 break-words whitespace-pre-wrap">
                    <span :class="item.correct === true ? 'text-green-700' : 'text-red-600/80'">{{ displayAnswer(item.answer) }}</span>
                    <span class="mx-1 text-gray-400">→</span>
                    <span class="text-green-700">{{ displayAnswer(item.correctAnswer) }}</span>
                </p>
            </div>
        </div>

        <div class="hidden md:block overflow-x-auto w-full">
            <table class="min-w-full border-collapse border border-gray-300">
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
                        <td class="border border-gray-300 px-4 py-2 whitespace-pre-wrap">{{ item.answer }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ item.correctAnswer }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <span v-if="item.correct === true" class="text-green-500 text-xl">✔</span>
                            <span v-else-if="item.correct === false" class="text-red-500 text-xl">✘</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 md:mt-6 flex justify-center">
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
    import { computed } from 'vue'
    // Libraries
    import { Link as InertiaLink } from '@inertiajs/vue3'

    // Props
    const props = defineProps({
        test: {type: Object},
    })

    const testData = computed(() => {
        return props.test ? JSON.parse(props.test.questions_and_answers) : null
    })

    function displayAnswer(value) {
        if (value === null || value === undefined || String(value).trim() === '') {
            return '—'
        }

        return value
    }
</script>
