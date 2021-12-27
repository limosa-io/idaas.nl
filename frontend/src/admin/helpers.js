
export const getAccessToken = function(){
    return window.sessionStorage.getItem('access_token');
}

export const getDecodedAccesstoken = function(){
    return JSON.parse(atob(getAccessToken().split('.')[1]))
}