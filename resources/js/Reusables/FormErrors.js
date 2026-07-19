import { ref } from 'vue'

export const useFormErrors = () => {
    const errors = ref({})

    function clearErrors(){
        errors.value = {}
    }

    function setErrors(nextErrors){
        errors.value = nextErrors
    }

    function fieldError(key){
        const messages = errors.value[key]

        return Array.isArray(messages) && messages.length > 0 ? messages[0] : null
    }

    function applyErrorResponse(errorResponse){
        if(errorResponse?.status === 422 && errorResponse?.data?.errors){
            errors.value = errorResponse.data.errors
            return
        }

        errors.value = {
            form: [errorResponse?.data?.message || errorResponse?.data?.msg || 'Something went wrong. Please try again.'],
        }
    }

    const formError = () => fieldError('form')

    return {
        errors,
        clearErrors,
        setErrors,
        fieldError,
        formError,
        applyErrorResponse,
    }
}
