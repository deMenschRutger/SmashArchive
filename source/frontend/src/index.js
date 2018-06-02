window.fbAsyncInit = function () {
    FB.init({
        appId: '1878227255734015',
        xfbml: false,
        version: 'v3.0'
    });
    FB.getLoginStatus(function (response) {
        console.log(response);
    });
    // document.getElementById('login').addEventListener('click', function () {
    //     FB.login(function(response) {
    //         if (response.authResponse) {
    //             console.log(response);
    //         } else {
    //             console.log('User cancelled login or did not fully authorize.');
    //         }
    //     });
    // });
};
