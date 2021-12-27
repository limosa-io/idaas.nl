<!--

1. Create ability to add languages
2. Always start with en_US by default.
3. Create ability to set default language!
4. 

-->

<template>

<div class="container-fluid">

  <h4 class="c-grey-900 mt-1 mb-3">Languages</h4>

  <div class="row">
    <div class="col-md-12">
      <div class="bgc-white bd bdrs-3 p-3 mt-2">

        <p>You can add multiple languages for that allow users to show the login page in their own language.</p>

        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col">Language Tag</th>
              <th scope="col" style="width: 180px;"></th>
            </tr>
          </thead>
          <tbody>
          
            <tr v-for="(l, index) of languages" :key="index">
              <td>{{ l }}</td>
              <td>
                <router-link tag="button" type="button" class="btn btn-dark" :to="`/internationalization/${l}`">
                  Edit
                </router-link>

                <button type="button" class="btn btn-danger ml-2" v-if="languages.length > 1" @click="deleteLanguage(index)">
                  Delete
                </button>

              </td>
            </tr>

            <tr v-if="!languages || languages.length == 0">
              <td colspan="2">
                There are no languages configured.
              </td>
            </tr>

          </tbody>

        </table>


        <form class="form-inline" @submit.prevent="add">

          <div class="form-group">
            <label for="locale" class="mr-2">Add language</label>


            <multiselect class="form-control language-select" v-model="locale" label="label" track-by="value"  :options="availableTags" placeholder="Select one">
              <template slot="singleLabel" slot-scope="props">
                {{ props.option.label }} - <strong>{{ props.option.value }}</strong>
              </template>
              <template slot="option" slot-scope="props">
                {{ props.option.label }} - <strong>{{ props.option.value }}</strong>
              </template>
            </multiselect>

          </div>

          <button class="btn btn-primary ml-2" @click.prevent="add">Add</button>

        </form>


      </div>
    </div>
  </div>
</div>

</template>

<script>

const RFC5646_LANGUAGE_TAGS = [
  { value: 'af', label: 'Afrikaans' } ,
{ value: 'af-ZA', label: 'Afrikaans (South Africa)' } ,
{ value: 'ar', label: 'Arabic' } ,
{ value: 'ar-AE', label: 'Arabic (U.A.E.)' } ,
{ value: 'ar-BH', label: 'Arabic (Bahrain)' } ,
{ value: 'ar-DZ', label: 'Arabic (Algeria)' } ,
{ value: 'ar-EG', label: 'Arabic (Egypt)' } ,
{ value: 'ar-IQ', label: 'Arabic (Iraq)' } ,
{ value: 'ar-JO', label: 'Arabic (Jordan)' } ,
{ value: 'ar-KW', label: 'Arabic (Kuwait)' } ,
{ value: 'ar-LB', label: 'Arabic (Lebanon)' } ,
{ value: 'ar-LY', label: 'Arabic (Libya)' } ,
{ value: 'ar-MA', label: 'Arabic (Morocco)' } ,
{ value: 'ar-OM', label: 'Arabic (Oman)' } ,
{ value: 'ar-QA', label: 'Arabic (Qatar)' } ,
{ value: 'ar-SA', label: 'Arabic (Saudi Arabia)' } ,
{ value: 'ar-SY', label: 'Arabic (Syria)' } ,
{ value: 'ar-TN', label: 'Arabic (Tunisia)' } ,
{ value: 'ar-YE', label: 'Arabic (Yemen)' } ,
{ value: 'az', label: 'Azeri (Latin)' } ,
{ value: 'az-AZ', label: 'Azeri (Latin) (Azerbaijan)' } ,
{ value: 'az-Cyrl-AZ', label: 'Azeri (Cyrillic) (Azerbaijan)' } ,
{ value: 'be', label: 'Belarusian' } ,
{ value: 'be-BY', label: 'Belarusian (Belarus)' } ,
{ value: 'bg', label: 'Bulgarian' } ,
{ value: 'bg-BG', label: 'Bulgarian (Bulgaria)' } ,
{ value: 'bs-BA', label: 'Bosnian (Bosnia and Herzegovina)' } ,
{ value: 'ca', label: 'Catalan' } ,
{ value: 'ca-ES', label: 'Catalan (Spain)' } ,
{ value: 'cs', label: 'Czech' } ,
{ value: 'cs-CZ', label: 'Czech (Czech Republic)' } ,
{ value: 'cy', label: 'Welsh' } ,
{ value: 'cy-GB', label: 'Welsh (United Kingdom)' } ,
{ value: 'da', label: 'Danish' } ,
{ value: 'da-DK', label: 'Danish (Denmark)' } ,
{ value: 'de', label: 'German' } ,
{ value: 'de-AT', label: 'German (Austria)' } ,
{ value: 'de-CH', label: 'German (Switzerland)' } ,
{ value: 'de-DE', label: 'German (Germany)' } ,
{ value: 'de-LI', label: 'German (Liechtenstein)' } ,
{ value: 'de-LU', label: 'German (Luxembourg)' } ,
{ value: 'dv', label: 'Divehi' } ,
{ value: 'dv-MV', label: 'Divehi (Maldives)' } ,
{ value: 'el', label: 'Greek' } ,
{ value: 'el-GR', label: 'Greek (Greece)' } ,
{ value: 'en', label: 'English' } ,
{ value: 'en-AU', label: 'English (Australia)' } ,
{ value: 'en-BZ', label: 'English (Belize)' } ,
{ value: 'en-CA', label: 'English (Canada)' } ,
{ value: 'en-CB', label: 'English (Caribbean)' } ,
{ value: 'en-GB', label: 'English (United Kingdom)' } ,
{ value: 'en-IE', label: 'English (Ireland)' } ,
{ value: 'en-JM', label: 'English (Jamaica)' } ,
{ value: 'en-NZ', label: 'English (New Zealand)' } ,
{ value: 'en-PH', label: 'English (Republic of the Philippines)' } ,
{ value: 'en-TT', label: 'English (Trinidad and Tobago)' } ,
{ value: 'en-US', label: 'English (United States)' } ,
{ value: 'en-ZA', label: 'English (South Africa)' } ,
{ value: 'en-ZW', label: 'English (Zimbabwe)' } ,
{ value: 'eo', label: 'Esperanto' } ,
{ value: 'es', label: 'Spanish' } ,
{ value: 'es-AR', label: 'Spanish (Argentina)' } ,
{ value: 'es-BO', label: 'Spanish (Bolivia)' } ,
{ value: 'es-CL', label: 'Spanish (Chile)' } ,
{ value: 'es-CO', label: 'Spanish (Colombia)' } ,
{ value: 'es-CR', label: 'Spanish (Costa Rica)' } ,
{ value: 'es-DO', label: 'Spanish (Dominican Republic)' } ,
{ value: 'es-EC', label: 'Spanish (Ecuador)' } ,
{ value: 'es-ES', label: 'Spanish (Spain)' } ,
{ value: 'es-GT', label: 'Spanish (Guatemala)' } ,
{ value: 'es-HN', label: 'Spanish (Honduras)' } ,
{ value: 'es-MX', label: 'Spanish (Mexico)' } ,
{ value: 'es-NI', label: 'Spanish (Nicaragua)' } ,
{ value: 'es-PA', label: 'Spanish (Panama)' } ,
{ value: 'es-PE', label: 'Spanish (Peru)' } ,
{ value: 'es-PR', label: 'Spanish (Puerto Rico)' } ,
{ value: 'es-PY', label: 'Spanish (Paraguay)' } ,
{ value: 'es-SV', label: 'Spanish (El Salvador)' } ,
{ value: 'es-UY', label: 'Spanish (Uruguay)' } ,
{ value: 'es-VE', label: 'Spanish (Venezuela)' } ,
{ value: 'et', label: 'Estonian' } ,
{ value: 'et-EE', label: 'Estonian (Estonia)' } ,
{ value: 'eu', label: 'Basque' } ,
{ value: 'eu-ES', label: 'Basque (Spain)' } ,
{ value: 'fa', label: 'Farsi' } ,
{ value: 'fa-IR', label: 'Farsi (Iran)' } ,
{ value: 'fi', label: 'Finnish' } ,
{ value: 'fi-FI', label: 'Finnish (Finland)' } ,
{ value: 'fo', label: 'Faroese' } ,
{ value: 'fo-FO', label: 'Faroese (Faroe Islands)' } ,
{ value: 'fr', label: 'French' } ,
{ value: 'fr-BE', label: 'French (Belgium)' } ,
{ value: 'fr-CA', label: 'French (Canada)' } ,
{ value: 'fr-CH', label: 'French (Switzerland)' } ,
{ value: 'fr-FR', label: 'French (France)' } ,
{ value: 'fr-LU', label: 'French (Luxembourg)' } ,
{ value: 'fr-MC', label: 'French (Principality of Monaco)' } ,
{ value: 'gl', label: 'Galician' } ,
{ value: 'gl-ES', label: 'Galician (Spain)' } ,
{ value: 'gu', label: 'Gujarati' } ,
{ value: 'gu-IN', label: 'Gujarati (India)' } ,
{ value: 'he', label: 'Hebrew' } ,
{ value: 'he-IL', label: 'Hebrew (Israel)' } ,
{ value: 'hi', label: 'Hindi' } ,
{ value: 'hi-IN', label: 'Hindi (India)' } ,
{ value: 'hr', label: 'Croatian' } ,
{ value: 'hr-BA', label: 'Croatian (Bosnia and Herzegovina)' } ,
{ value: 'hr-HR', label: 'Croatian (Croatia)' } ,
{ value: 'hu', label: 'Hungarian' } ,
{ value: 'hu-HU', label: 'Hungarian (Hungary)' } ,
{ value: 'hy', label: 'Armenian' } ,
{ value: 'hy-AM', label: 'Armenian (Armenia)' } ,
{ value: 'id', label: 'Indonesian' } ,
{ value: 'id-ID', label: 'Indonesian (Indonesia)' } ,
{ value: 'is', label: 'Icelandic' } ,
{ value: 'is-IS', label: 'Icelandic (Iceland)' } ,
{ value: 'it', label: 'Italian' } ,
{ value: 'it-CH', label: 'Italian (Switzerland)' } ,
{ value: 'it-IT', label: 'Italian (Italy)' } ,
{ value: 'ja', label: 'Japanese' } ,
{ value: 'ja-JP', label: 'Japanese (Japan)' } ,
{ value: 'ka', label: 'Georgian' } ,
{ value: 'ka-GE', label: 'Georgian (Georgia)' } ,
{ value: 'kk', label: 'Kazakh' } ,
{ value: 'kk-KZ', label: 'Kazakh (Kazakhstan)' } ,
{ value: 'kn', label: 'Kannada' } ,
{ value: 'kn-IN', label: 'Kannada (India)' } ,
{ value: 'ko', label: 'Korean' } ,
{ value: 'ko-KR', label: 'Korean (Korea)' } ,
{ value: 'kok', label: 'Konkani' } ,
{ value: 'kok-IN', label: 'Konkani (India)' } ,
{ value: 'ky', label: 'Kyrgyz' } ,
{ value: 'ky-KG', label: 'Kyrgyz (Kyrgyzstan)' } ,
{ value: 'lt', label: 'Lithuanian' } ,
{ value: 'lt-LT', label: 'Lithuanian (Lithuania)' } ,
{ value: 'lv', label: 'Latvian' } ,
{ value: 'lv-LV', label: 'Latvian (Latvia)' } ,
{ value: 'mi', label: 'Maori' } ,
{ value: 'mi-NZ', label: 'Maori (New Zealand)' } ,
{ value: 'mk', label: 'FYRO Macedonian' } ,
{ value: 'mk-MK', label: 'FYRO Macedonian (Former Yugoslav Republic of Macedonia)' } ,
{ value: 'mn', label: 'Mongolian' } ,
{ value: 'mn-MN', label: 'Mongolian (Mongolia)' } ,
{ value: 'mr', label: 'Marathi' } ,
{ value: 'mr-IN', label: 'Marathi (India)' } ,
{ value: 'ms', label: 'Malay' } ,
{ value: 'ms-BN', label: 'Malay (Brunei Darussalam)' } ,
{ value: 'ms-MY', label: 'Malay (Malaysia)' } ,
{ value: 'mt', label: 'Maltese' } ,
{ value: 'mt-MT', label: 'Maltese (Malta)' } ,
{ value: 'nb', label: 'Norwegian (Bokm?l)' } ,
{ value: 'nb-NO', label: 'Norwegian (Bokm?l) (Norway)' } ,
{ value: 'nl', label: 'Dutch' } ,
{ value: 'nl-BE', label: 'Dutch (Belgium)' } ,
{ value: 'nl-NL', label: 'Dutch (Netherlands)' } ,
{ value: 'nn-NO', label: 'Norwegian (Nynorsk) (Norway)' } ,
{ value: 'ns', label: 'Northern Sotho' } ,
{ value: 'ns-ZA', label: 'Northern Sotho (South Africa)' } ,
{ value: 'pa', label: 'Punjabi' } ,
{ value: 'pa-IN', label: 'Punjabi (India)' } ,
{ value: 'pl', label: 'Polish' } ,
{ value: 'pl-PL', label: 'Polish (Poland)' } ,
{ value: 'ps', label: 'Pashto' } ,
{ value: 'ps-AR', label: 'Pashto (Afghanistan)' } ,
{ value: 'pt', label: 'Portuguese' } ,
{ value: 'pt-BR', label: 'Portuguese (Brazil)' } ,
{ value: 'pt-PT', label: 'Portuguese (Portugal)' } ,
{ value: 'qu', label: 'Quechua' } ,
{ value: 'qu-BO', label: 'Quechua (Bolivia)' } ,
{ value: 'qu-EC', label: 'Quechua (Ecuador)' } ,
{ value: 'qu-PE', label: 'Quechua (Peru)' } ,
{ value: 'ro', label: 'Romanian' } ,
{ value: 'ro-RO', label: 'Romanian (Romania)' } ,
{ value: 'ru', label: 'Russian' } ,
{ value: 'ru-RU', label: 'Russian (Russia)' } ,
{ value: 'sa', label: 'Sanskrit' } ,
{ value: 'sa-IN', label: 'Sanskrit (India)' } ,
{ value: 'se', label: 'Sami' } ,
{ value: 'se-FI', label: 'Sami (Finland)' } ,
{ value: 'se-NO', label: 'Sami (Norway)' } ,
{ value: 'se-SE', label: 'Sami (Sweden)' } ,
{ value: 'sk', label: 'Slovak' } ,
{ value: 'sk-SK', label: 'Slovak (Slovakia)' } ,
{ value: 'sl', label: 'Slovenian' } ,
{ value: 'sl-SI', label: 'Slovenian (Slovenia)' } ,
{ value: 'sq', label: 'Albanian' } ,
{ value: 'sq-AL', label: 'Albanian (Albania)' } ,
{ value: 'sr-BA', label: 'Serbian (Latin) (Bosnia and Herzegovina)' } ,
{ value: 'sr-Cyrl-BA', label: 'Serbian (Cyrillic) (Bosnia and Herzegovina)' } ,
{ value: 'sr-SP', label: 'Serbian (Latin) (Serbia and Montenegro)' } ,
{ value: 'sr-Cyrl-SP', label: 'Serbian (Cyrillic) (Serbia and Montenegro)' } ,
{ value: 'sv', label: 'Swedish' } ,
{ value: 'sv-FI', label: 'Swedish (Finland)' } ,
{ value: 'sv-SE', label: 'Swedish (Sweden)' } ,
{ value: 'sw', label: 'Swahili' } ,
{ value: 'sw-KE', label: 'Swahili (Kenya)' } ,
{ value: 'syr', label: 'Syriac' } ,
{ value: 'syr-SY', label: 'Syriac (Syria)' } ,
{ value: 'ta', label: 'Tamil' } ,
{ value: 'ta-IN', label: 'Tamil (India)' } ,
{ value: 'te', label: 'Telugu' } ,
{ value: 'te-IN', label: 'Telugu (India)' } ,
{ value: 'th', label: 'Thai' } ,
{ value: 'th-TH', label: 'Thai (Thailand)' } ,
{ value: 'tl', label: 'Tagalog' } ,
{ value: 'tl-PH', label: 'Tagalog (Philippines)' } ,
{ value: 'tn', label: 'Tswana' } ,
{ value: 'tn-ZA', label: 'Tswana (South Africa)' } ,
{ value: 'tr', label: 'Turkish' } ,
{ value: 'tr-TR', label: 'Turkish (Turkey)' } ,
{ value: 'tt', label: 'Tatar' } ,
{ value: 'tt-RU', label: 'Tatar (Russia)' } ,
{ value: 'ts', label: 'Tsonga' } ,
{ value: 'uk', label: 'Ukrainian' } ,
{ value: 'uk-UA', label: 'Ukrainian (Ukraine)' } ,
{ value: 'ur', label: 'Urdu' } ,
{ value: 'ur-PK', label: 'Urdu (Islamic Republic of Pakistan)' } ,
{ value: 'uz', label: 'Uzbek (Latin)' } ,
{ value: 'uz-UZ', label: 'Uzbek (Latin) (Uzbekistan)' } ,
{ value: 'uz-Cyrl-UZ', label: 'Uzbek (Cyrillic) (Uzbekistan)' } ,
{ value: 'vi', label: 'Vietnamese' } ,
{ value: 'vi-VN', label: 'Vietnamese (Viet Nam)' } ,
{ value: 'xh', label: 'Xhosa' } ,
{ value: 'xh-ZA', label: 'Xhosa (South Africa)' } ,
{ value: 'zh', label: 'Chinese' } ,
{ value: 'zh-CN', label: 'Chinese (S)' } ,
{ value: 'zh-HK', label: 'Chinese (Hong Kong)' } ,
{ value: 'zh-MO', label: 'Chinese (Macau)' } ,
{ value: 'zh-SG', label: 'Chinese (Singapore)' } ,
{ value: 'zh-TW', label: 'Chinese (T)' } ,
{ value: 'zu', label: 'Zulu' } ,
{ value: 'zu-ZA', label: 'Zulu (South Africa)' } 
];

export default {

  data(){

    return {
      languages: [],


      availableTags: RFC5646_LANGUAGE_TAGS,

      // for adding
      locale: null
    }
  },

  mounted(){

    this.$http.get(this.$murl('api/settings?namespace=ui')).then(response => {
      this.languages = response.data.languages || [];
    });

  },
  methods: {

    deleteLanguage(index){
      this.languages.splice(index, 1);
      this.save();
    },

    add(){

      let languagesNew = this.languages.slice();
      languagesNew.push(this.locale.value);

      this.save(languagesNew).then(r => {
        
      });

    },

    save(languages = null){

      this.$http.put(this.$murl('api/settings/bulk?namespace=ui'), {
        languages: (languages ? languages : this.languages).sort()
      }).catch(response => {
        this.$noty({
          text: 'We could not save this. You may have already added this language.'
        });
      }).then(r => {
        
        this.languages = r.body.languages;

        return r;
      });

    }


  }

}
</script>

<style lang="scss">

body .form-inline .form-control.language-select{
  width: 300px;
}

</style>
