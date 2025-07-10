$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="tooltip-html"]').tooltip({html: true});
    $('[data-external-app]').click(function(e) {
        if(parent === window) {
            return;
        }
        e.preventDefault();
        parent.postMessage({
            type: "navigateExternalApp",
            app:  $(this).data('external-app'),
            url:  $(this).attr('href')
        }, '*');
    });
    if(window.parent && window.parent !== window) {
        setTimeout(function(){
            $('.sf-toolbarreset, .sf-minitoolbar').css('bottom', '36px');
        }, 2000);
    }

});
