/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-09 16:49:00
 */

$(document).ready(function() {

    // if no startup errors, hide splashscreen
    if(!window.azimut.hasInitialError) {
        setTimeout(function() {
            var azimutBackofficeLoader = $('#azimutBackofficeLoader');
            azimutBackofficeLoader.fadeOut("slow", function() {
                azimutBackofficeLoader.remove();
            });
        }, 3000);
    }
});

$(document).ready(function() {
    // detect touchscreens (actually it detects touch event support and not touchscreen)
    function isTouchDevice() {
        // chrome desktop has ontouchstart event on if developper tools are opened
        return (('ontouchstart' in window) || (navigator.MaxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0));
    }

    if (isTouchDevice()) {
        $('body').addClass('coarse-pointer');
    }

    /* HACK: disable page scroll for ios */
    // disable touchmove bubbling on document
    $(document).on('touchmove',function(e) {
        e.preventDefault();
    });
    // Uses body because jQuery on events are called off of the element they are
    // added to, so bubbling would not work if we used document instead.
    $('body').on('touchstart', '.scrollable', function(e) {
        if (e.currentTarget.scrollTop === 0) {
            e.currentTarget.scrollTop = 1;
        } else if (e.currentTarget.scrollHeight === e.currentTarget.scrollTop + e.currentTarget.offsetHeight) {
            e.currentTarget.scrollTop -= 1;
        }
    });
    // Stops preventDefault from being called on document if it sees a scrollable div
    $('body').on('touchmove', '.scrollable', function(e) {
        e.stopPropagation();
    });
    /* END HACK */

    /* HACK: resolve safari mobile height bug in ios7 with iPad */
    if (navigator.userAgent.match(/iPad;.*CPU.*OS 7_\d/i)) {
        $('html').height(window.innerHeight);
        //window.scrollTo(0, 0);
    }
    /* END HACK */

});

function fullScreen() {
    var element = document.documentElement;
    if (element.requestFullscreen)
        if (document.fullScreenElement) {
            document.cancelFullScreen();
        } else {
            element.requestFullscreen();
        }
    else if (element.webkitRequestFullscreen)
        if (document.webkitFullscreenElement) {
            document.webkitCancelFullScreen();
        } else {
            element.webkitRequestFullscreen();
        }
    else if (element.mozRequestFullScreen)
        if (document.mozFullScreenElement) {
            document.mozCancelFullScreen();
        } else {
            element.mozRequestFullScreen();
        }
    else if (element.msRequestFullscreen)
        if (document.msFullscreenElement) {
            document.msExitFullscreen();
        } else {
            element.msRequestFullscreen();
        }
}
