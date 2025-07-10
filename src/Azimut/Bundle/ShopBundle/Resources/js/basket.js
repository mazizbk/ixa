/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-03 12:12:13
 */

(function($) {
    // /!\ Warning : this has to reflect az_shop_price twig filter
    var formatPrice = function(amount) {
        amount = parseFloat(amount ? amount / 100 : 0).toFixed(2).replace('.', ','); // @TODO use localized money format
        return amount + ' €';
    };

    $(document).ready(function() {
        $('[data-basket-add-item-url]').on('click', function(event) {
            event.preventDefault();

            $.post($(this).data('basketAddItemUrl'), {
                'class': $(this).data('basketItemClass'),
                'id': $(this).data('basketItemId')
            }).done(function(data) {
                if ('blocked' == data.status) {
                    if (data.statusMessage) {
                        $('#shopProductNotAddedToBasketMessage').text(data.statusMessage);
                        $('#shopProductNotAddedToBasket').show();
                    }
                }
                else {
                    $('#shopBasketCount').text(data.basketItemsCount);
                    $('#shopProductAddedToBasket').show();
                }
            }).fail(function() {
                $('#shopProductAddToBasketError').show();
            });
        });

        var storeQuantityValue = function(target) {
            target.data('prev-value', target.val());
        }

        $('[data-basket-update-item-quantity-url]').on('focusin', function() {
            storeQuantityValue($(this));
        });

        $('[data-basket-update-item-quantity-url]').on('change', function() {
            var target = $(this);
            var targetTotalPrice = $('#' + $(this).data('targetTotalPrice'));
            // Don't cancel previous request because there are not aborted on server, and we don't know in wich order they will be processed
            // Instead we set a delay before sending the request and cancel the previous timer on next call
            if (Window.azShopBasketUpdateItemQuantityXHRTimeout) {
                clearTimeout(Window.azShopBasketUpdateItemQuantityXHRTimeout);
            }
            Window.azShopBasketUpdateItemQuantityXHRTimeout = setTimeout(function () {
                $.ajax({
                    url: target.data('basketUpdateItemQuantityUrl'),
                    method: "PATCH",
                    data: {
                        'quantity': target.val()
                    },
                }).done(function(data) {
                    if ('blocked' == data.status) {
                        if (data.statusMessage) {
                            $('#shopBasketProductQuantityNotChangedMessage').text(data.statusMessage);
                            $('#shopBasketProductQuantityNotChanged').show();
                        }
                        target.val(target.data('prev-value')); // Restore previous value
                    }
                    else {
                        if (true == data.hasAddedOrDeletedOrderItems) {
                            window.location.reload(); // Reload page to reveal items added or removed by backoffice events -> @TODO : should be better done with XHR
                        }
                        target.val(data.quantity); // Force set view value from API
                        var amount = data.quantity * data.price;
                        targetTotalPrice.text(formatPrice(amount));
                        $('#basketTotalPrice').text(formatPrice(data.order_total_amount));
                    }
                }).fail(function() {
                    $('#shopBasketProductQuantityError').show();
                    target.val(target.data('prev-value')); // Restore previous value
                });
            }, 300);
        });

        $('[data-basket-decrease-item-quantity]').on('click', function() {
            var target = $('#' + $(this).data('basketDecreaseItemQuantity'));
            storeQuantityValue(target);
            if (target.val() > 1) {
                target.val(parseInt(target.val()) - 1);
                target.trigger("change");
            }
        });

        $('[data-basket-increase-item-quantity]').on('click', function() {
            var target = $('#' + $(this).data('basketIncreaseItemQuantity'));
            storeQuantityValue(target);
            target.val(parseInt(target.val()) + 1);
            target.trigger("change");
        });

        $('[data-basket-delete-item-url]').on('click', function() {
            var target = $(this);
            $.ajax({
                url: target.data('basketDeleteItemUrl'),
                method: "DELETE"
            }).done(function(data) {
                $('#' + target.data('basketDeleteItemTarget')).remove();
                $('#basketTotalPrice').text(formatPrice(data.order_total_amount));
            }).fail(function() {
            });
        });
    });
})(jQuery);
