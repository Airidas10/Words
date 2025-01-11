export const useAxiosRequest = () => {
    function axiosRequest(endpoint, apiData, fetchMethod, customHandler=false){
        return new Promise((resolve, reject) => {
            axios({
                method: fetchMethod,
                url: endpoint,
                data: apiData
            })
            .then((response) => {
                if(response.data.status == 'success'){
                    if(customHandler){
                        resolve(response)
                    } else {
                        resolve(response.data)
                    }
                } else{
                    if(customHandler){
                        resolve(response)
                    } else {
                        alert("Error. Something went wrong!")
                    }
                }
            })
            .catch(function (error) {
                if(customHandler) {
                    reject(error.response)
                } else {
                    alert("Error. Something went wrong!")
                }
            })
        })
    }

    return { axiosRequest }
}