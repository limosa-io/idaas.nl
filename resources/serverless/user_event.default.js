// default user_event code

function main({
  before, // the old user object (SCIM representation)
  after, // the new user object (SCIM representation)
  type, // Could be create, replace, patch, delete
  me, // if the user used the SCIM /Me endpoint
  callback,
  variables
}) {

  // Try attributes 'emails', 'activate', 'roles', etc.
  if(attributeChanged(before, after, 'emails')){
    callback(
      [
        // Uncomment the line below and change the email template id and to-address
        // new Email(variables.emailTemplate0.id).to('example@maildu.de'),
      ]
    );
  }else{
    callback(
      [
        // do nothing, but still invoke the callback-function. Elsewise, the script will never end (well... it will timeout)
      ]
    );
  }

}
