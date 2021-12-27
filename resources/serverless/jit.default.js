
// default jit code

function main ( {subject, context} ){
  
    return {

        // Return a SCIM representation of the user
        user: {
            email: subject.email
        }
    }
}

exports.main = main;
  
