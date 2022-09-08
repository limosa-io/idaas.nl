<html>
<body>
<script type="text/javascript">

function inIframe () {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

settings = @json($settings);

var target = null;

//FIXME: Mostly needed for popup. However, what if auto-redirect after iframe load?
if(inIframe()){
    // This is the case for popup windows.
    // document.top is not always available. Therefore, post message to top. Let it redirect
    parent.postMessage({
        type: 'redirect',
        location: settings.location
    },settings.target);
}else if (window.opener){
    // FIXME: AND it is a redirect back to the UI Server ...
        
    try{
        window.opener.postMessage({
            type: 'refresh_state',
        });
    }catch(error){
        
    }

    setTimeout(function(){
        document.location = settings.location;
    }, 400);

}else{
    document.location = settings.location;
}

if(target != null){
    console.log('Post message!');
    target.postMessage({
        type: 'redirect',
        location: settings.location
    },settings.target);
}else{
    
}

</script>
</body>
</html>