
import settings from './settings'

export const toLogin = function(){

    var state = Math.random().toString(36).substring(2);
    var nonce = Math.random().toString(36).substring(2);

    window.localStorage.setItem('state', state);

    var url = settings.authorization_endpoint + '?response_type=code&client_id=' + window.encodeURIComponent(settings.client_id) + '&redirect_uri=' + window.encodeURIComponent(settings.redirect_uri) + '&scope=openid+applications:manage&state=' + window.encodeURIComponent(state) + '&nonce=' + nonce;

    document.location = url;
    
}

export const toLogout = function(){

    var url = settings.end_session_endpoint + '?post_logout_redirect_uri=' + window.encodeURIComponent(settings.post_logout_uri);

    document.location = url;


}

export const startAccountLinking = function(acrValue){

    var state = Math.random().toString(36).substring(2);
    var nonce = Math.random().toString(36).substring(2);

    window.localStorage.setItem('state_account_linking', state);

    var url = settings.authorization_endpoint + '?response_type=code&prompt=login&client_id=' + window.encodeURIComponent(settings.client_id) + '&redirect_uri=' + window.encodeURIComponent(settings.redirect_uri_account_linking) + '&scope=openid&state=' + window.encodeURIComponent(state) + '&nonce=' + nonce + '&acr_values=' + window.encodeURIComponent(acrValue);

    document.location = url;
    
}

export const noty = function(message, type = 'info'){

    import ( /* webpackChunkName: "noty" */ 'noty').then(module => {

        new module.default({
            theme: 'bootstrap-v4',
            text: message,
            timeout: 10000,
            animation: {},
            layout: 'bottomCenter',
            type: type,
            closeWith: ['button','click'],
            progressBar: true,
        }).show();

    });

}
