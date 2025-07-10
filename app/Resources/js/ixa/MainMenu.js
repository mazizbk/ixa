/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-04-19 12:11:04
 */

$(document).ready(function() {
    // Main menu button
    $('.MainMenu-btn').click(function() {
        $(this).parent('.MainMenu').toggleClass('open');
    });
    $('.MainMenu-btn').mouseup(function() {
        $(this).blur();
    });
    $(document).click(function(event) {
        if(!$(event.target).closest('.MainMenu').length) {
            if($('.MainMenu').hasClass('open')) {
                $('.MainMenu').removeClass('open');
            }
        }
    });

    $('.MainMenu > .MainMenu-items .MainMenu-item-link').click(function(event) {
        var item = $(this).closest('.MainMenu-item');

        if (item.hasClass('open')) {
            item.removeClass('open');
        }
        else {
            $('.MainMenu-item').removeClass('open');
            item.addClass('open');
        }
    });

    // In mobile menu, deactivate link when item has more than one children items
    $('.hasMoreThanOneChildren > .MainMenu-item-link').click(function(event){
        if(window.matchMedia('(max-width: 930px), (pointer: coarse)').matches) {
            event.preventDefault();
        }
    });
});
