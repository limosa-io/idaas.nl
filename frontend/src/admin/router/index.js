import Vue from 'vue'
import Router from 'vue-router'

import CompleteLogin from '../components/CompleteLogin.vue'
import InitLogin from '../components/InitLogin.vue'
import InitLogout from '../components/InitLogout.vue'
import Admin from '../components/Admin.vue'
import Tenants from '../components/Tenants.vue'

import Tester from '../components/Tester.vue'
import Error from '../components/Error.vue'

import Logout from '../components/Logout.vue'

import Help from '../components/admin/Help.vue'

import SAML from '../components/admin/SAML.vue'
import ListSAML from '../components/admin/saml/List.vue'
import EditSAML from '../components/admin/saml/Edit.vue'
import NewSAML from '../components/admin/saml/New.vue'
import SettingsSAML from '../components/admin/saml/Settings.vue'
import GeneralSettingsSAML from '../components/admin/saml/settings/General.vue'
import ImportSAML from '../components/admin/saml/Import.vue'

const OIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/OIDC.vue')
const NewOIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/oidc/New.vue')
const EditOIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/oidc/Edit.vue')
const ListOIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/oidc/List.vue')
const SettingsOIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/oidc/Settings.vue')
const TestOIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/oidc/Test.vue')
const GeneralSettingsOIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/oidc/settings/General.vue')
const ScopesSettingsOIDC = () => import(/* webpackPrefetch: true *//* webpackChunkName: "oidc" */ '../components/admin/oidc/settings/Scopes.vue')

const PageNotFound = () => import(/* webpackPrefetch: true *//* webpackChunkName: "pageNotFound" */ '../components/PageNotFound.vue')

import KeysSettingsOIDC from '../components/admin/oidc/settings/Keys.vue'
import ClaimsSettingsOIDC from '../components/admin/oidc/settings/Claims.vue'
import LevelsSettingsOIDC from '../components/admin/oidc/settings/Levels.vue'

import Users from '../components/admin/Users.vue'
import ListUser from '../components/admin/users/List.vue'
import NewUser from '../components/admin/users/New.vue'
import EditUser from '../components/admin/users/Edit.vue'

import Groups from '../components/admin/Groups.vue'
import ListGroup from '../components/admin/groups/List.vue'
import NewGroup from '../components/admin/groups/New.vue'
import EditGroup from '../components/admin/groups/Edit.vue'

import Registration from '../components/admin/Registration.vue'

import Email from '../components/admin/Email.vue'
import ListEmail from '../components/admin/email/List.vue'
import EditEmail from '../components/admin/email/Edit.vue'
import Authentication from '../components/admin/Authentication.vue'
import AuthenticationList from '../components/admin/authentication/List.vue'
import AuthenticationNew from '../components/admin/authentication/New.vue'
import AuthenticationEdit from '../components/admin/authentication/Edit.vue'

import UserInterface from '../components/admin/UserInterface.vue'
import UserInterfaceDesign from '../components/admin/userinterface/Design.vue'
import UserInterfaceMedia from '../components/admin/userinterface/Media.vue'
import UserInterfaceServers from '../components/admin/userinterface/Servers.vue'

import Rules from '../components/admin/Rules.vue'
import RulesList from '../components/admin/rules/List.vue'
import RulesEdit from '../components/admin/rules/Edit.vue'

import Sessions from '../components/admin/Sessions.vue'
import ListSessions from '../components/admin/sessions/Sessions.vue'
import ListTokens from '../components/admin/sessions/Tokens.vue'
import ListSubjects from '../components/admin/sessions/Subjects.vue'

import Internationalization from '../components/admin/Internationalization.vue'
import InternationalizationList from '../components/admin/internationalization/List.vue'
import InternationalizationEdit from '../components/admin/internationalization/Edit.vue'

import Webhook from '../components/admin/Webhook.vue'

Vue.use(Router);

export default new Router({
  mode: 'history',
  base: '/',
  routes: [

    // {
    //   path: '/tenants',
    //   component: Tenants

    // },

    {
      path: '/',
      component: Admin,

      meta: {
        label: 'Home'
      },
      
      children: [
        {
          path: '/',
          name: 'dashboard',
          component: () => import('../components/admin/Dashboard.vue'),
          style: {
            icon: 'c-blue-500 ti-home'
          },

          meta: {
            label: 'Dashboard'
          },
        },

        {
          path: '/oidc',
          component: OIDC,
          

          style: {
            icon: 'c-teal-500 ti-view-grid'
          },

          meta: {
            label: 'Applications',
            hideChildren: true,
          },

          children: [
            {
              path: '',
              component: ListOIDC,
              name: 'oidc.clients.list',

              meta: {
                label: 'Clients'
              }

            },
            {
              path: '/applications/oidc/add',
              name: 'oidc.clients.new',
              component: NewOIDC,

              meta: {
                label: 'New'
              },

            },
            {
              path: '/applications/oidc/edit/:client_id',
              name: 'oidc.client.edit',
              
              meta: {
                label: 'Edit'
              },

              component: EditOIDC,
            },

            {
              path: '/applications/oidc/test',
              
              component: TestOIDC,
              
              meta: {
                label: 'Test'
              },


            },

            {
              path: '/applications/oidc/settings',
              
              component: SettingsOIDC,

              meta: {
                label: 'Settings'
              },

              children: [
                {
                  path: '',
                  component: GeneralSettingsOIDC,
                  name: 'oidc.settings.general'
                },
                {
                  path: '/applications/oidc/settings/scopes',
                  name: 'oidc.settings.scopes',
                  component: ScopesSettingsOIDC,
                },
                {
                  path: '/applications/oidc/settings/levels',
                  name: 'oidc.settings.levels',
                  component: LevelsSettingsOIDC,
                },
                {
                  path: '/applications/oidc/settings/claims',
                  name: 'oidc.settings.claims',
                  component: ClaimsSettingsOIDC,
                },
                {
                  path: '/applications/oidc/settings/keys',
                  name: 'oidc.settings.keys',
                  component: KeysSettingsOIDC,
                }

              ]

            }
            
          ]

        },
    
        {
          
          component: Users,
          path: '/users',
          meta: {
            hideChildren: true,
            label: 'Users'
          },
          style: {
            icon: 'c-deep-orange-500 ti-user'
          },
          
          children: [
            {
              path: '/users/:page(\\d+)?',
              component: ListUser,
              name: 'users.list',
              meta: {
                label: 'Users'
              }
            },
            {
              path: '/users/add',
              name: 'users.new',
              component: NewUser,

              meta: {
                label: 'New User'
              },

            },
            {
              path: '/users/edit/:user_id',
              name: 'users.edit',

              meta: {
                label: 'Edit User'
              },

              component: EditUser,
            }
            
          ]
    
        },

        {
          
          component: Groups,
          path: '/groups',
          meta: {
            hideChildren: true,
            label: 'Groups'
          },
          style: {
            icon: 'blue-grey-400 ti-credit-card'
          },
          
          children: [
            {
              path: '/groups/:page(\\d+)?',
              component: ListGroup,
              name: 'groups.list',
              meta: {
                label: 'Groups'
              }
            },

            {
              path: '/groups/add',
              name: 'groups.new',
              component: NewGroup,

              meta: {
                label: 'New Group'
              },

            },
            {
              path: '/groups/edit/:group_id',
              name: 'groups.edit',

              meta: {
                label: 'Edit Group'
              },

              component: EditGroup,
            }
           
            
          ]
    
        },

        {
          
          component: Sessions,
          path: '/sessions',
          meta: {
            hideChildren: true,
            label: 'Sessions'
          },
          style: {
            icon: 'blue-grey-300 ti-time'
          },
          
          children: [
            {
              path: '',
              component: ListSessions,
              name: 'sessions.sessions',
              meta: {
                label: 'Sessions'
              }
            },
            {
              path: '/sessions/tokens',
              component: ListTokens,
              name: 'sessions.tokens',
              meta: {
                label: 'Tokens'
              }
            },

            {
              path: '/sessions/subjects/:page(\\d+)?',
              component: ListSubjects,
              name: 'sessions.subjects',
              meta: {
                label: 'Subjects'
              }
            }
          ]
        },

        {
          path: '/registration',
          component: Registration,
          name: 'users.registration',

          meta: {
            label: 'Registration'
          },

          style: {
            icon: 'c-deep-red-500  ti-id-badge'
          },

        },
        
        {
          path: '/email',
          component: Email,

          style: {
            icon: 'c-deep-purple-500 ti-email'
          },

          meta: {
            hideChildren: true,
            label: 'Emails',
          },

          children: [
            {
              path: '',
              name: 'emails.list',
              component: ListEmail,

              meta: {
                label: 'Email Templates'
              }

            },

            {
              path: '/emails/edit/:object_id',
              name: 'emails.edit',
              component: EditEmail,

              meta: {
                label: 'Edit Template'
              }
            }

          ]



        },
    
        {
          path: '/authentication',
          component: Authentication,
          

          meta: {
            hideChildren: true,
            label: 'Authentication'
          },
          
          style: {
            icon: 'c-brown-500 ti-lock'
          },

          children: [
            {
              path: '',
              name: 'authentication.list',
              component: AuthenticationList,
              hide: true
            },
            {
              path: '/authentication/add',
              name: 'authentication.new',
              component: AuthenticationNew,
              hide: true,

              meta: {
                label: 'New'
              }

            },

            {
              path: '/authentication/edit/:module_id',
              name: 'authentication.edit',
              component: AuthenticationEdit,
              hide: true,

              meta: {
                label: 'Edit'
              }

            }

            
          ]
        },
    
        {
          path: '/user-interface',
          component: UserInterface,
          style: {
            icon: 'pink-400 ti-image'
          },

          meta: {
            hideChildren: true,
            label: 'User Interface'
          },

          children: [

            {
              path: '',
              name: 'userinterface.servers',
              component: UserInterfaceServers,

              meta: {
                label: 'Servers'
              },
            },

            {
              path: 'design',
              name: 'userinterface.design',
              component: UserInterfaceDesign,
              meta: {
                label: 'Design'
              }
            },

            {
              path: 'manager',
              name: 'userinterface.manager',
              component: UserInterfaceMedia,

              meta: {
                label: 'Manager'
              },
            }

            

          ]
        },
    
        {
          path: '/rules',
          component: Rules,
          style: {
            icon: 'c-deep-orange-900 ti-control-forward'
          },

          meta: {
            hideChildren: true,
            label: 'Rules'
          },

          children: [

            {
              path: '',
              name: 'rules.list',
              component: RulesList,

            },

            {
              path: '/rules/:rule_id',
              name: 'rules.edit',
              component: RulesEdit,

              meta: {
                label: 'Edit'
              }

            },

            // {
            //   path: 'attributes',
            //   name: 'rules.attributes',
            //   component: RulesAttributes,

            //   meta: {
            //     label: 'Attributes'
            //   }

            // },

          ]

        },

        {
          path: '/webhooks',
          name: 'Webhooks',
          component: Webhook,
          style: {
            icon: 'c-brown-500 ti-share'
          }
        },

        {
          path: '/saml',
          component: SAML,

          meta: {
            label: 'SAML',
            hideChildren: true
          },

          style: {
            icon: 'c-brown-500 ti-cloud'
          },

          children: [
            {
              path: '',
              name: 'saml.serviceproviders.list',
              component: ListSAML,

              meta: {
                label: ''
              }

            },
            {
              path: '/applications/saml/edit/:id',
              name: 'saml.serviceproviders.edit',
              component: EditSAML,

              meta: {
                label: 'Edit'
              }

            },
            {
              path: '/applications/saml/add',
              name: 'saml.serviceproviders.add',
              component: NewSAML,

              meta: {
                label: 'Add'
              },

            },
            {
              path: '/applications/saml/import',
              name: 'saml.serviceproviders.import',
              component: ImportSAML,

              meta: {
                label: 'Import metadata'
              },

            },

            {
              path: '/applications/saml/settings',
              component: SettingsSAML,

              meta: {
                label: 'Settings'
              },

              children: [
                {
                  path: '',
                  component: GeneralSettingsSAML,
                  name: 'saml.settings.general'
                }
              ]

            }
          ]
        },

        {
          path: '/internationalization',
          component: Internationalization,

          meta: {
            label: 'Language',
            hideChildren: true
          },

          style: {
            icon: 'c-brown-500 ti-world'
          },

          children: [
            {
              path: '',
              name: 'internationalization.list',
              component: InternationalizationList,

              meta: {
                label: 'List'
              }

            },
            {
              path: '/internationalization/:locale',
              name: 'internationalization.edit',
              component: InternationalizationEdit,

              meta: {
                label: 'Edit'
              }

            },
          ]

        },

      ]
    
    },

    {
      path: '/tester',
      name: 'tester',
      component: Tester,
      hide: true
    },
    
    {
      path: '/logout',
      name: 'logout',
      component: Logout,
      hide: true
    },

    {
      path: '/completelogin',
      name: 'CompleteLogin',
      component: CompleteLogin
    },

    {
      path: '/initlogin',
      name: 'initlogin',
      component: InitLogin
    },

    {
      path: '/initlogout',
      name: 'initlogout',
      component: InitLogout
    },

    {
      path: '/error',
      name: 'error.default',
      component: Error
    },

    {
      path: '/*',
      name: '404',
      component: PageNotFound
    },

    
    
  ]
})
