
export default {
    client_id: import.meta.env.VITE_CLIENT_ID,
    redirect_uri: import.meta.env.VITE_REDIRECT_URI,
    post_logout_uri: import.meta.env.VITE_POST_LOGOUT_URI,
    authorization_endpoint: import.meta.env.VITE_AUTHORIZATION_ENDPOINT,
    token_endpoint: import.meta.env.VITE_TOKEN_ENDPOINT,
    tenants_endpoint: import.meta.env.VITE_TENANTS_ENDPOINT,

    userinfo_endpoint: import.meta.env.VITE_USERINFO_ENDPOINT,
    redirect_uri_account_linking: import.meta.env.VITE_REDIRECT_URI_ACCOUNT_LINKING,

    scim_endpoint: import.meta.env.VITE_SCIM_ENDPOINT,
    update_email_endpoint: import.meta.env.VITE_UPDATE_EMAIL_ENDPOINT,

    end_session_endpoint: import.meta.env.VITE_END_SESSION_ENDPOINT,
    hotp_generator_endpoint: import.meta.env.VITE_HOTP_GENERATOR_ENDPOINT,
    fido_get_create_arguments_endpoint: import.meta.env.VITE_FIDO_GET_CREATE_ARGUMENTS_ENDPOINT,
    fido_register_endpoint: import.meta.env.VITE_FIDO_REGISTER_ENDPOINT,
    fido_list_keys_endpoint: import.meta.env.VITE_FIDO_LIST_KEYS_ENDPOINT,
    fido_endpoint: import.meta.env.VITE_FIDO_ENDPOINT,

    session_target_origin: import.meta.env.VITE_SESSION_TARGET_ORIGIN

}