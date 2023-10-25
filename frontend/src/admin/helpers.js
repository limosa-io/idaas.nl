import axios from "axios";

export const maxios = axios.create({
    baseURL: window.manageUrls.manage,
    timeout: 5000,
});

maxios.interceptors.request.use(function (config) {
    config.headers.Authorization = 'Bearer ' + window.sessionStorage.getItem('access_token');
    return config;
});

export const oidcUrl = window.manageUrls.oidc;
export const manageUrl = window.manageUrls.manage;

// returns complete url based on oidcUrl as base url
export const getOidcUrl = function (path) {
    return oidcUrl + path;
}

export const laxios = axios.create({
    baseURL: window.manageUrls.oidc,
    timeout: 5000,
});

laxios.interceptors.request.use(function (config) {
    if (!config.public) {
        config.headers.Authorization = 'Bearer ' + window.sessionStorage.getItem('access_token');
    }
    return config;
});

// interceptor for axios that retries the requires in case of a failure
maxios.interceptors.response.use(undefined, refresh_token_if_needed);
laxios.interceptors.response.use(undefined, refresh_token_if_needed);



function refresh_token_if_needed(err) {
    const originalRequest = err.config;
    if (err.response.status === 401 && !originalRequest._retry) {

        originalRequest._retry = true;
        return laxios.post('/token', {
            grant_type: 'refresh_token',
            'refresh_token': window.sessionStorage.getItem('refresh_token'),
            client_id: window.manageClient.clientId
        }, { public: true }).then(res => {
            if (res.status === 200) {
                window.sessionStorage.setItem('access_token', res.data.access_token);
                window.sessionStorage.setItem('refresh_token', res.data.refresh_token);
                return this(originalRequest);
            }
        });
        

    }
    return Promise.reject(err);

}

export const getAccessToken = function () {
    return window.sessionStorage.getItem('access_token');
}

export const getDecodedAccesstoken = function () {
    return JSON.parse(atob(getAccessToken().split('.')[1]))
}

export const notify = function (properties) {

    properties.timeout = 3000;
    properties.animation = {};
    properties.layout = 'bottomCenter';
    properties.type = 'success';
    properties.progressBar = true;

    import( /* webpackChunkName: "noty" */ 'noty').then(module => {

        new module.default(properties).show();
    });

}
