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
    config.headers.Authorization = 'Bearer ' + window.sessionStorage.getItem('access_token');
    return config;
});

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
