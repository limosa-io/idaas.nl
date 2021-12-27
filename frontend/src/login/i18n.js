import Vue from 'vue'
import VueI18n from 'vue-i18n'
import VueResource from 'vue-resource'

Vue.use(VueResource);
Vue.use(VueI18n)

const loadedLanguages = [];

var lastPressed = null;

export const i18n = new VueI18n({
  
});

let old = null;

document.onkeydown = (e) => {
    if(e.altKey){
        
        old = i18n.locale != 'i18n' ? i18n.locale : old;

        if(lastPressed != null && (lastPressed - (new Date()).getTime() ) > 300){
            i18n.locale = i18n.locale != 'i18n' ? 'i18n' : old;
            lastPressed = 0;
        }

        lastPressed = Math.max( (new Date()).getTime() + 300, lastPressed + 300 );
        
    }
};

export function switchLanguage(locale) {

    return new Promise( (resolve, reject) => {
        
        if(loadedLanguages.indexOf(locale) >= 0){
            i18n.locale = locale;
            localStorage.setItem('locale', locale);
            resolve(locale);
        }else{

            Vue.http.get(
                `/api/language/${locale}?version=${encodeURIComponent(window.information.resources_version)}`
            ).then( r => {
                i18n.setLocaleMessage(locale, r.data);

                i18n.locale = locale;
                
                localStorage.setItem('locale', locale);
                
                resolve(r.data);
            }).catch(e => {
                reject(e);
            });

        }

    });

}

switchLanguage(localStorage.getItem('locale') || 'en-GB');
