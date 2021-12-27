// default attribute code
function ({
  subject,
  context,
  callback,
  variables
}) {

  callback(

    {
      attributes: {
        subject: subject,
        context: context
      }
    }
    
  );

}
