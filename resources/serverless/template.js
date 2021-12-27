/**
 * If you save this code, ensure all existing cloud functions get redeployed
 */
class Email {

    constructor(template_id) {
        this.type = 'mail';
        this.template_id = template_id;
        this.data = {};
    }

    to(to){
        this.to = to;

        return this;
    }

    setData(data){
        this.data = data;

        return this;
    }

    toUser(userId){
        this.toUserId = userId;

        return this;
    }

}

function isActive(after){

    return after != null && after['urn:ietf:params:scim:schemas:core:2.0:User']['active'];

}

function attributeChanged(before, after, attribute){

    if(before == null){
        return false;
    }

    if(after == null){
        return false;
    }

    return JSON.stringify(before['urn:ietf:params:scim:schemas:core:2.0:User'][attribute]) != JSON.stringify(after['urn:ietf:params:scim:schemas:core:2.0:User'][attribute]);

}

function groupAdded(before, after, group){
    // check if groups and one of groups.value is group
    let matchBefore = JSON.stringify(before['urn:ietf:params:scim:schemas:core:2.0:User'].groups || []).indexOf(group.id) > -1;

    let matchAfter = JSON.stringify(after['urn:ietf:params:scim:schemas:core:2.0:User'].groups || []).indexOf(group.id) > -1;

    return !matchBefore && matchAfter;

}

async function main( arguments ){

    return new Promise( (resolve, reject) => {

        (

        // start user code
        function ( {user, context, callback} ) {
        
            callback(
                new Email('123').to('someone@asdggas.nl')
            );
        
        }
        // end user code

        )( { 
            ...arguments, 
            variables: arguments['variables']['[cloud_function_id]'] || arguments.variables || {},
            callback: resolve
        } );

    }).then( r => {
        return {
            ...arguments,
            results: (arguments.results || []).concat( Array.isArray(r) ? r : [r])
        };
    });

}

exports.main = main;