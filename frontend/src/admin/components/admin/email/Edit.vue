
<template>

<div>
  <div class="bgc-white bd bdrs-3 p-3 mt-2">

    <div class="row">

      <div class="col-md-6">

        <form ref="templateForm" :class="{'was-validated': wasValidated}" v-if="object" class="needs-validation" novalidate v-on:submit.prevent="onSubmit">

            <!-- !object.is_parent &&  -->
            <div class="form-group">
            <label for="parent">Base template</label>
             <b-form-select id="parent" @change="reloadPreview" aria-describedby="parentHelp" v-if="parents" value-field="id" text-field="name" v-model="object.parent_id" :options="parentOptions"
            class="" />
            <small id="parentHelp" class="form-text text-muted">Consider using a parent template that defines the header, main and footer section.</small>
          </div>

          <div class="form-group">
            <label for="name">Name</label>
            <input :class="{'is-invalid': errors['name'] ? true : false}" type="text" class="form-control" v-model="object.name" id="name" aria-describedby="nameHelp" placeholder="Template name">
            <div v-if="errors['name']" class="invalid-feedback" v-for="(e,index) in errors['name']" :key="index">
                {{ e }}
              </div>
            <small id="nameHelp" class="form-text text-muted">The name is not visible for end users. Use for your own reference.</small>
          </div>

          <div class="form-group">
            <label for="name">Subject</label>
            <input :class="{'is-invalid': errors['subject'] ? true : false}" type="text" class="form-control" v-model="object.subject" id="subject" aria-describedby="subjectHelp" placeholder="Subject">
            <div v-if="errors['subject']" class="invalid-feedback" v-for="(e,index) in errors['subject']" :key="index">
                {{ e }}
              </div>
            <small id="subjectHelp" class="form-text text-muted">The subject of the e-mail.</small>
          </div>

          <div class="form-group">
            <label for="codemirror">Body</label>

            <codemirror v-on:changes="reloadPreview" id="codemirror" v-model="object.body" :options="cmOptions"></codemirror>

          </div>



          <div class="alert alert-info" role="alert">
            Define sections<br /><br />
            <pre><span v-pre>{{$ content }}
&#x3C;p&#x3E;{{ article.body }}&#x3C;/p&#x3E;
{{/ content }}</span></pre>
            And use translations.<br /><br />
            <pre><span v-pre>{{#t}}login.remember{{/t}}</span></pre>
          </div>


          <button type="submit" class="btn btn-primary">Save</button>


        </form>

      </div>

      <div class="col-md-6">

        <iframe ref="preview" sandbox="" class="w-100 h-100">

        </iframe>

      </div>

    </div>

  </div>


  <div class="card border-danger mb-3 mt-3" v-if="object && !object.default">
    <div class="card-header">Danger Zone</div>
    <div class="card-body text-danger">
      <p class="card-text">Clicking the button below will delete this application. This cannot be undone.</p>
      <button type="button" class="btn btn-danger" @click="deleteObject(object)">Delete</button>
    </div>
  </div>

</div>

</template>


<script>

import _ from 'lodash'

export default {

  components: {
    'codemirror': (resolve) =>
      import ( /* webpackChunkName: "vue-codemirror" */ '../../lib/codemirror.js').then((m) => {
        resolve(m.default.codemirror);
      })
  },

  data() {
    return {

      cmOptions: {
        tabSize: 4,
        mode: 'text/javascript',
        theme: 'lucario',
        lineNumbers: true,
        line: true,
      },

      parents: null,

      preview: null,

      errors: {},

      wasValidated: false,
      loading: false,

      object: null


    }
  },

  computed: {
    parentOptions: function () {
      var result = this.parents;
      result.unshift({
        name: '--- no parent ---',
        id: null
      });
      return this.parents ? result : [];
    }
  },

  mounted() {

    this.$http.get(this.$murl('api/mail_template/' + encodeURIComponent(this.$route.params.object_id))).then(response => {
      this.object = response.data;
      this.wasValidated = false;

      this.reloadPreviewNow();

    }, response => {
      this.errors = response.data.errors;
      this.wasValidated = true;
    });

    this.$http.get(this.$murl('api/mail_template')).then(response => {

      this.parents = response.data;

    }, response => {
      // error callback
    });

  },

  watch: {

  },

  methods: {

    reloadPreviewNow() {

      if (!this.$refs.templateForm || this.$refs.templateForm.checkValidity()) {

        this.$http.post(this.$murl('api/preview_mail_template/' + encodeURIComponent(this.object.id)),
          this.object
        ).then(response => {

          this.$refs.preview.contentWindow.location.replace('data:text/html;base64,' + Buffer.from(response.data.preview).toString('base64'));

          this.errors = {};

        }, response => {
          this.errors = response.data.errors;
          this.wasValidated = true;

          this.$noty({
            text: 'We could not preview this.',
            type: 'error'
          });
        });

      } else {
        this.wasValidated = true;
        this.$noty({
          text: 'We could not preview this.',
          type: 'error'
        });
      }

    },

    reloadPreviewDebounced: _.debounce(function () {



      this.reloadPreviewNow();


    }, 1000),

    reloadPreview() {
      this.reloadPreviewDebounced();
    },

    deleteObject(o) {


      this.$http.delete(this.$murl('api/mail_template/' + encodeURIComponent(this.object.id))).then(response => {

        this.$noty({
          text: 'Successfully deleted the client.'
        });

        this.$router.replace({
          name: 'emails.list'
        });

      });

    },

    onSubmit(event) {

      if (event.target.checkValidity()) {
        this.$http.put(this.$murl('api/mail_template/' + encodeURIComponent(this.object.id)),
          this.object
        ).then(response => {

          this.$noty({
            text: 'We have succesfully saved this.'
          });
          this.errors = {};

        }, response => {
          this.errors = response.data.errors;
          this.wasValidated = true;

          this.$noty({
            text: 'We could not save this.',
            type: 'error'
          });
        });

      } else {
        this.wasValidated = true;
        this.$noty({
          text: 'We could not save this.',
          type: 'error'
        });
      }

      event.preventDefault();

    }

  }

}
</script>

<style lang="scss" scoped>

iframe{
    border-style: none;
}

</style>
