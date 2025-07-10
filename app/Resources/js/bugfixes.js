/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-12-01 14:37:27
 */

// Fix Firefox iframe cache bug. Cf. https://bugzilla.mozilla.org/show_bug.cgi?id=356558
$(document).ready(function() {
    $('iframe').each(function () {
        if($(this)[0].src && $(this)[0].src != '') {
            $(this)[0].contentWindow.location.href = $(this)[0].src;
        }
    });
});
