
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

    session_target_origin: import.meta.env.VITE_SESSION_TARGET_ORIGIN

}