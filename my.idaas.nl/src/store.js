
export default {
    state: {
        accessToken: null,

        accessDenied: false,

        user: null
    },
    mutations: {
        accessToken(state, accessToken) {
            state.accessToken = accessToken
        },

        accessDenied(state, accessDenied){
            state.accessDenied = accessDenied;
        },

        user(state, user){
            state.user = user;
        }
    },

    actions: {
        saveUser({ user }){
            
            

        }
    }
};

