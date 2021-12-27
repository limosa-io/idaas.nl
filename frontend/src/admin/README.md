# admin

## Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```




# TODO:

* ~~Mogelijk maken om OAuth client as "trusted" te markeren. Dan automatisch autoriseren.~~
* ~~Autorisatie schermen integreren in 'AuthChain'.~~
* ~~Mogelijk maken om 'levels' op te slaan per module.~~
* ~~Client Credentials Grant testen. Werkt dit?~~
* ~~Op "Profile" weergeven welke 'links' iemand heeft.~~
* ~~In AuthResponse/State opnemen welke scopes én attributen gevraagd zijn.~~
* ~~Mogelijk maken om default 'levels' te definieren op Client/SP ~~
* ~~OIDC ClientRepository implementeren. Dan ook beter mogelijk om te koppelen aan ACR levels.~~
* ~~Session ID linken aan access token. Dan mogelijk om in te trekken na logout => extend ArieTimmerman\Passport\Bridge\AccessTokenRepository. Scope 'online_access' introduceren~~
* ~~Mogelijk maken om 'Do not allow override levels' op te geven.~~
* ~~Mogelijk maken om te approven. Consent Module verder uitwerken. Zoals: https://developers.google.com/accounts/images/approvaldevice.png~~
* ~~Mogelijk maken om client te "testen". Vragen om toestemming om "redirect uri" toe te voegen.~~
* ~~Mogelijk "This is not me" te selecteren en cookieloos te beginnen~~
* ~~Mogelijk maken om basis UI dingetjes in te stellen > Logo, Titel, CSS?, Theme?, Colors?~~
* ~~jwks endpoint toevoegen~~
* ~~Een user_metadata en een app_metadata attribute maken~~
* ~~keys kunnen beheren voor OIDC. Import X509 / create self-signed~~
* ~~Checken hoe het nu ziet met Subject, Link en inloggen van Users via andere manieren ...~~
* ~~Check naken in AuthChain: Niet toestaan dat loops aangemaakt worden.~~
* ~~AuthChain / State fixen. Nu op basis van State ID. Lijkt of hij de eerste verwerkingen niet opslaat ...~~
* ~~Fix AuthModule opslaan~~
* ~~Waarom werkt passwordless na registratie niet, als passwordless ook meteen mag?~~
* ~~Inform two-factor about subject~~
* ~~Zorgen dat mail werkt via Mailgun. Mail templates voor activatie en passwordless login maken~~
* ~~Iets doen met SCIM. Laten instellen wat is toegestaan op het /Me endpoint. Wat bij create, wat bij patch? => PolicyDecisionPoint implementeren.~~
* ~~Setting: Activate account when this authentication level is reached: bronze, gold, silver ...~~
* ~~Test activatie email => komt 2 keer. Hoe voorkomen?~~
* ~~Alleen OAuth flow succesvol maken als requested state gehaald is~~
* ~~Store log-in sessions in database.~~
* ~~Controleren of id_token nu met acr values gevult is. Deze vullen met juiste waardes.~~
* ~~Routes samenvoegen in Laravel. api/web. Domeinen. Denk aan middleware!!~~
* ~~Binding probleem oplossen. Tenant niet doorgeven aan onderliggende routes. Mogelijk oplossen door "url" niet te matchen. Maar te setten met $subdomain.ice.test  >  https://laracasts.com/discuss/channels/laravel/how-to-stop-subdomain-route-group-parameter-get-injected-into-every-underlying-controller-method ~~
* ~~Inloggen met oidc. Instellingen in index.html~~
* ~~Hoe rollen van Subject veilig gebruiken? Rol 'slug' introduceren (i.e. id, value, displayName)~~
* ~~Weergave 'edit application' verbeteren.~~
* ~~Hoe mapping scopes => claims => SCIM attributes ( table met 3 kolommen. Scope, claim, flattend_scim ) ~~
* ~~Consent opslaan voor subject. Heeft gewerkt. Is ergens kapot gegaan.~~
* ~~2FA makkelijker maken. (1) Standaard chain niet afsluiten ALS er 2FA mogelijk is. (2) AuthModule automatisch disablen als geen phone, OTP, mail, facebook, google, ... ~~
* ~~Interceptor pushen voor al het andere. Buiten models om??~~
* ~~Chrome kan niet inloggen?? => Origin whitelist fixen. Gaat ~~
* ~~Methode "autoRedirect(): boolean" en "getRedirectResponse()" toevoegen op Authmodules. Handig voor OIDC, Facebook, Google etc.~~
* ~~UIServer configureerbaar maken. Waar?? Aparte tabel?~~
* ~~Gegeven: een tenant is een client van een andere tenant! :O Automatisch "remote" toevoegen bij aanmaken tenant.~~
* ~~This in Laravel ICE als dependency hangen?? > Vue CLI gebruiken.~~
* ~~Create 1 standaard e-mailtemplate. Met tekst en knop. Standaard laten gebruiken door ALLE mailing systemen (Passwordless, account-maken)~~
* ~~"display group" niet configureerbaar maken. Default goed zetten.~~
* ~~Policy probleem oplossen. Waarom geen rollen terug? Check logs. Gaat iets mis met query?~~
* ~~Waarom verkeerde Subject-class in authenticated event?? Nodig om stats op te slaan.~~
* ~~Authmodules / chain versimpelen. "Start" module niet kunnen aanpassen. "Consent" module niet kunnen toevoegen.~~
* ~~Zorgen dat auth chain altijd met "start" begint > validatie toevoegen bij aanpassen/bewerken~~
* ~~Zoeken op email mogelijk maken via SCIM~~
* ~~Store state details in login session.  First for checking. Later for listing and log out.~~
* ~~Make registration work! => Proof-of-creation~~
* ~~CORS fixen. Niet met OIDC middleware. x-proof-scim-asdgdas toestaan. Nooit * toestaan. Altijd check.~~
* ~~Email fixen van mailgun. Dan Password-reset fixen. ~~
* ~~Email template fixen voor Activatie, Password Reset en ....~~
* ~~Weergave "Google Authenticator" verbeteren. 2FA / 1st-factor markeren.~~
* ~~Webhook systeem optuigen? Quiable? Simple Guzzle request. Met user object.~~
* ~~Search fixen???.~~
* ~~Zorgen dat "build watch" goed gaat. Blade templates terugzetten~~
* ~~AuthChain krijgen met getInstance!~~
* ~~Edit OIDC clients netjes maken. (allow override etc)~~
* ~~Rules example > UserInfo endpoint. Javascript example code + highlight~~
* ~~client_credentials met rollen fixen. Hoe client of user krijgen bij calls? Role assignment fixen.~~
* ~~Oplossen "InvalidArgumentException: Route [login] not defined. in file /Users/arie/sites/ice/vendor/laravel/framework/src/Illuminate/Routing/UrlGenerator.php on line 372"~~
* ~~Client opslaan met 'name'. Uitlezen en schrijven naar client_name met getter/mutators?~~
* ~~dubbele request bij users en applications oplossen~~
* ~~Melding weergeven: No tenant on subdomain ...~~
* ~~iframe problemen oplossen~~
* ~~Consent module is "not allowed?"~~
* ~~Password-forgotten en magic link standaard op master tenant plaatsen.~~
* ~~OIDC error handling fixen!~~
* ~~Logo-weergave checken. Standaard een logo op master-tenant.~~
* ~~Privacy / Terms linkje weergeven op login?~~
* ~~Tenant overview pagina maken.~~
* ~~Tenant endpoint maken. Alleen door users van master?~~
* ~~Account linking door ... => (1) OIDC met acr_values=google|facebook, (2) Userinfo aan te roepen met nieuw access_token (3) SCIM call om Link toe te voegen met "sub" uit userinfo-endpoint! Toevoegen als subject not ongelinkt is.~~
* ~~In default tenant Facebook toevoegen?? => 246166902616833 / 939b2a2a25fd2b24c98e6de5e8629bd9~~
* ~~Authentication level automatisch op basis van module type~~
* ~~Hoe users matchen/linken? Hoe zat het ook alweer ...~~
* ~~Use loginhint voor activation. > Op die manier mogelijk om activatielink te gebruiken.~~
* ~~Loggen we wel in met username? Username minder aanwezig maken?~~
* ~~Groepen introduceren. Daarmee ook 'activatie nodig' op te lossen ...~~
* ~~New tenant fixen. Master zonder user aanmaken. Daarna user.~~
* ~~Mogelijk maken om te blokkeren als gebruiker niet van groep lid is. In ModuleTrait@process. Zodra subject bekend is.~~
* ~~Facebook user aanmaken bij geen match. Testen met test-users.~~
* ~~Introduce introspect endpoint.~~
* ~~Hoelang session 'authRequest' bewaren. En hoelang State?~~
* ~~No cookies/sessions op {tenant}.manage.ice.test~~
* ~~Config cache laten werken. Geen closures in config.~~
* ~~SCIM: Checken dat header Content-Type: application/json+sicm aanwezig is.~~
* ~~Pagination voor users/groups fixen: Bij zoeken resetten naar pagina 1. Meerdere items per pagina gat niet goed.~~
* ~~Logo fixen.~~
* ~~StabableInterface een queue meegeven. Bij "save" alles opslaan. Of een normale DB insert doen.~~
* ~~TokenGuard cache instellen.~~
* ~~Token events afvangen. Aanmaken / revoken. Repository overschrijven??? => Via Observer => https://laravel.com/docs/5.6/eloquent~~
* ~~Cache op 'ice' niveau instellen. Niet in modules.~~
* ~~Uitzoeken waar "[2018-10-11 20:18:57] production.ERROR: Validation failed. Errors: []" vandaan komt ...~~
* ~~Check of redis gebruikt wordt~~
* ~~validations doen op tenant niveau. Nieuwe validatie introduceren?~~
* ~~RememberStorage => opslaan werkt. Ophalen niet...~~
* ~~"rememeber me" van modules opslaan in session??~~
* ~~Waarom wordt session niet weggeschreven???? "database" werkt" databaseWithCache niet ...~~
* ~~Set-up Subject re-use. Door eloquentSubject uuid op te slaan in ... ~~
* ~~OIDC logout flow afronden. Eerst > Logout centraal. Redirect terug.~~
* ~~Session store tenant_id specifiek maken~~
* ~~Session/token overzicht ui afronden. Mogelijkheid om zoeken op user...~~
* ~~Per module kunnen opslaan: Remember for session. Or for cookie. Max length ~~
* ~~Fix password reset.~~
* ~~groep toevoegen fixen.~~
* ~~Fix scope-weergave. Labeltje voor "applications:manage" verschijnt. Oplossen met 'init' in Consent module. Consent module naar 'ice' verplaatsen.~~
* ~~Problemen met Tenant in command. Zetten in Kernel werkt niet bij 1-ste migrate.~~
* ~~Foutmelding "something bad happend" oplossen. Na cancel van login voor manager.~~
* ~~SCIM: unique-test faalt voor groepen. Verkeerde query.~~
* ~~Eenvoudig 'events' dashboard maken. (aantal logins per dag, populariteit modules?,   ) =>StatD ooit gebruiken?~~
* ~~Auto init default login bij 'You're on the login page, but we're not sure where you want to go to next ...'~~
* ~~Foutafhandeling oidc flow. Eindeloze lus voorkomen ...~~
* ~~SAML tabel opnieuw aanmaken.~~
* ~~Pagina maken voor geen 'State'.~~
* ~~Logout fixen. Bij refresh staat er 'ok' => https://master.manage.ice.test/logout~~

* ~~registration fixen.~~
* ~~"scopes_supported" uit well-known wordt niet (meteen?) bijgewerkt na toevoegen scopes~~
* ~~config cache fixen => Probleem is dat Tenant (class) on runtime on config wordt opgeslagen. Dit in app container doen.~~
* ~~Bij verkeerde post_logout_redirect_uri foutmelding geven.~~

* ~~Registratie lijkt met password niet te werken ...~~
* ~~Login pagina mobile-friendly maken. Margins fixen~~
* ~~webhook logger testen.~~
* ~~Image uploader toevoegen => https://transloadit.com/. Alleen lijst van ids + urls bijhouden. Storage elders ... => https://transloadit.com/docs/#uppy~~

* ~~SCIM prepare-methode fixen.~~
* ~~Bij user interface, voorbeeld-items onthouden?? Title in het midden plaatsen. Verbergen bij klein scherm??~~
* ~~Bij delete van user, niet terug naar lijst~~
* ~~CloudFunction gebruiken voor shouldCreateUser~~
* ~~Zorgen voor seamless upgrade. Nu verwijdert "npm run build" de oude bestanden.~~
* ~~Auto redirect maken. Voor Facebook, en Google. En > 10 anderen.~~
* ~~"Rememeber me" laten werken voor password-reset, totp etc~~
* ~~S3 upload fixen voor uppy.~~
* ~~UISettings naar Core brengen. TenantSettings weer weg ... RegistrationSettings ook weg ...~~
* ~~Create Tenant->Settings (relation from Tenant). En Tenant->version. Voor caching taalbestanden, ui-settings, etc.~~
* ~~nonce fixen. View middleware misschien?~~

* ~~Mail templates verbeteren. (1) Create Main template. (2) Create password forgotten, activation, change email templates. (3) ensure these are used as defaults ~~
* ~~E-mail template instelbaar maken voor "password forgotten". Misschien default met name "password forgotten".~~
* ~~vue-tags: nog maar 1 plugin gebruiken.~~
* ~~i18n t-function maken in email templates~~

* ~~Rules endpoint fixen~~
* ~~Event endpoint maken. (1) na aanmaken user, (2) controleren welk attribute is aangepast, (3) eventueel mail sturen met link ("je account is nu actief ..." of "er is een nieuwe aanmelding")~~
* ~~postgres connection opzetten via Docker.~~
* ~~Controleren of uploaden werkt. (waarom niet?? Omdat hij rechtstreeks naar S3 moet, mogelijk verkeerde credentials)~~
* ~~Een "reload" by tenantns-overview page herlaat callback... hoe oplossen??~~
* ~~rollen-weergave verbeteren.~~
* ~~Je kunt maar 1 static::creating hebben. Problematisch ... ??~~
* ~~Sensible defaults voor registration, of andere settings. ZONDER! dingen naar database te schrijven?~~
* ~~Zorgen dat OpenIDConnect module alleen wordt weergegeven als daarom gevraagd. Optie introduceren: "show only if requested"~~
* ~~Beter "create user" scherm maken. Eisen dat of username of email ingevuld gaat worden~~
* ~~Soort 'demo app' maken?? Met 'test pagina' met logging??~~
* ~~Prevent locking out yourself by disallowing editing/removing modules used to authenticate with~~
* ~~Refresh tokens fixen. In Admin.vue en main.js~~
* ~~Translations: check for uniqueness~~
* ~~Zorg voor goede implemenatie cloud function sequences => maakt het mogelijk makkelijk standaard-snippets te gebruiken.~~
* ~~Per type, een 'lijstje maken met rules'. Sorteerbaar. Bij nieuwe toevoegen: (1) nieuwe opslaan, (2) sequence aanmaken of updated_at bijwerken ( $sequence->touch() ), (3) sequence_id als setting opslaan. Bij geen actieve rules, sequence setting leegmaken! In event? I.e. bij aanmaken cloudfunction dan ... bij de-activeren dan ...~~
* ~~Testen maken~~
* ~~Login met popup altijd via iframe. Gescheiden verantwoordelijkheiden. Form toestaan (frame-ancestors)~~
* ~~Client pagina praat alleen met iframe. iframe praat met oidc urls en authchain api.~~
* ~~Checkbox toevoegen => logout_uris syncen met redirect_uris.~~
* ~~Get allowed origins from script location. Does only work for sync loaded scripts~~
* ~~Open link in same tab. After one-time password link. => Not possible.~~
* ~~"Add test client button. Show popup (1) Creating client, (2) creating codesandbox, (3) updating client redirect_uri, (4) present link of codesandbox.~~
* ~~Created better looking "logged in" page for test application.~~
* ~~OTP mail template incorrect.~~
* ~~class Socialite not found. In Generic.php. On host. => simplesamlphp config/config.php is missing. what loads it?~~

* TODO: `issueAccessToken` uitbreiden. Ondersteuning voor `data` or something like that.
* Check spf records / spark post mail
* webhooks komen niet aan
* www.idaas.nl is gekoppeld aan login.
* webidaas.nl. Niet goed. Want daarom access denied
* Use groupAdded(before,after,variables.group0)
* HOTP module remember is broken
* Test Google Chrome support
* Encrypt state when to facebook/google/social ...
* redirect_uri en post_logout_redirect_uris als json indexeren. En daarop zoeken ...? Of in aparte tabel? Bredere ondersteuning.
* Per client een default UI Server in kunnen stellen?
* test case maken voor tenant aanmaken. (1) aanmaken, (2) inloggen
* test case maken voor web_hook

* Van de UI Servers een default instellen => deze niet laten applyen op 'master' client.
* Optie toevoegen aan "registration endpoint" dat SCIM Me alleen in registratie-flow aangroepen mag worden => check StateId.
* Makkelijker maken om User in te loggen. Api zoals Login::authenticate($user, levels)
* Makkelijker maken om rules toe te voegen. 1 klik per type. Daarna code snippet search oid? Met knopje "replace" current.
* Sentry koppelen => https://sentry.io/pricing/
* Werken met "assigned scope" en "authorized scopes". OF "assigned group" en "authorized groups" ??
* Allow sorting user list. Configurable column display
* Informatie weergeven bij gekoppelde accunts. Remote account identifier / attributes?
* Session overzicht maken. I.e. https://master.ice.test/session. Modules, devices (firefox, safari, ios) en tijdsduur laten zien.
* Voorbeeld met group-provisionnig maken.
* Makkelijk maken om access token te kopieren.
* Idee: UMS library publiceren. Mogelijk maken om login-scherm op eigen domein te tonen, in iframe.
* events tracken op basis van authchain events en user events. logins. access tokens. creations.
* jscryptospace diagram graph network bij authchain/tree => https://github.com/cytoscape/cytoscape.js-dagre
* Create hash for each subject. Prevent duplicates. But still allow multiple subjects from same source (because of different permissions perhaps).
    * Or not: Some subjects are unique everytime. For example because of access tokens. 


# Language aanpassen

1. Load defaults from manager.tenant.orange.io/api/language/defaults/en_GB => In dotted syntax!
2. Load customizations from manager.tenant.orange.io/api/language/customizations/en_GB
3. Store customizations. End up in table "internationalizations". Update tenant.version = 'W/123242'. Ensure version is loaded in frontend.
4. Let frontend load from tenant.orange.io/language/en_GB?hash=kjhghjkjh



Mobiel aanpassen flow

1. Invoke /update_mobile met (mobile) => send sms met OTP (123456)
2. Pass token to exchange point. Return access_token WITH extra attribute. And SIGN!
3. Pass the retrieved access_token to the SCIM provider

* Implement https://tools.ietf.org/html/draft-ietf-oauth-token-exchange-12 for activation tokens



~~Email aanpassen flow~~
~~1. Invoke /update_mail (authenticated) met (email) => send email met token => return ENCRYPTED token. Encrypt met >8 tekens? Base32 (ybndrfg8ejkmcpqxot1uwisza345h769) > https://philzimmermann.com/docs/human-oriented-base-32-encoding.txt~~
~~2. Pass token to exchange point (OAuth token exchange, one-time token),. Return access_token WITH extra attribute. And SIGN!~~
~~3. Pass the retrieved access_token to the SCIM provider~~


# test users

ewlbtircrq_1537469252@tfbnw.net / timmerman
erryivrenz_1537469267@tfbnw.net
wwnnneqkye_1537469257@tfbnw.net
dzltplxfcn_1537469263@tfbnw.net

Password change:

1. Require a JWT issued in the last 30 seconds?

* Tijdelijke data is
 - inlogstatus
 - moduleResults
 - state (tijdens inlogproces TOT moment van inloggen)


* Theme from: https://colorlib.com/polygon/adminator/index.html

# Documentation


# User data

Pre-defined set of attributes with a pre-defined set of validation rules.

TODO: You can stricten the validations by writing your own validation rules. You can use the user_metadata and the app_metadata attribute to populate extra attributes.

# 
