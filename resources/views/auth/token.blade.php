<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Auth Token</title>
</head>
<body>
<script>
(function(){
    // token and frontend are injected by the server
    const token = @json($token);
    const frontend = @json($frontend);

    try {
        if (window.opener && frontend) {
            // send token to opener (parent) and then close
            window.opener.postMessage({ type: 'social_auth_success', token: token }, frontend);
            window.close();
        } else if (window.opener) {
            // fallback: allow any origin if frontend not set (less secure)
            window.opener.postMessage({ type: 'social_auth_success', token: token }, '*');
            window.close();
        } else {
            // No opener: show token so developer can copy it
            document.body.innerText = 'Token: ' + token;
        }
    } catch (e) {
        document.body.innerText = 'Error posting token to opener: ' + e;
    }
})();
</script>
</body>
</html>
