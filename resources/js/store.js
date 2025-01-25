import { createStore } from 'vuex'

export default createStore({
    state: {
        searchData: null,
        showTranslation: true,
    },

    getters: {

    },

    actions: {

    },

    mutations: {
        setSearchData(state, searchData){
            state.searchData = searchData
        },

        setShowTranslation(state, showTranslation){
            state.showTranslation = showTranslation
        },
    }
})