/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-29 12:19:55
 ******************************************************************************
 *
 * Mediacenter TinyMCE plugin proxy through iframe
 * (loaded only if current page is in iframe)
 */

(function() {
    if (window.self !== window.top) {
        window.azimutExternalAppTinyMceSetup = function(editor) {
            // Plug widget through iframe
            window.addEventListener('message', function(evt) {
                if (undefined == evt.data[0] || undefined == evt.data[0].mediaType || null == window.azimut.mediacenterWidgetProxyId) {
                    return
                };

                // Only handles file type mediacenter proxy
                if ('tinymce' != window.azimut.mediacenterWidgetProxyType || editor.id != window.azimut.mediacenterWidgetProxyId) {
                    return;
                }

                // Insert parsed tag in editor
                editor.focus();
                editor.selection.setContent(evt.data[0].parsedTag);
            }, false);

            editor.addButton('mediacenter', {
                title : 'Mediacenter',
                icon : 'mediacenter',
                onclick : function() {
                    // Set active widget proxy
                    window.azimut.mediacenterWidgetProxyId = editor.id;
                    window.azimut.mediacenterWidgetProxyType = 'tinymce';

                    // Call widget on parent through iframe
                    parent.postMessage('showMediacenterWidget', '*');

                    return false;
                }
            });

            // Transform existing mediacenter tags to HTML
            editor.on('init', function() {
                var content = editor.getContent();
                if (content) {
                    parent.postMessage({
                        action: 'MediaDeclinationTagParser.tagsInTextToHtml',
                        returnAction: 'tinymceContentParsedToHtml',
                        text: content,
                        elementId: editor.id
                    }, '*');
                }
            });
        };

        // Async return from tag parser on each tinymce
        window.addEventListener('message', function(evt) {
            if ('tinymceContentParsed' == evt.data.action) {
                // Update TinyMCE content
                tinyMCE.get(evt.data.elementId).setContent(evt.data.text);

                // Set element has parsed
                var element = $('#' + evt.data.elementId);
                element.data('isParsingPending', false);
                element.addClass('is-parsed');

                // Retry submit
                element.closest('form').submit();
            }

            if ('tinymceContentParsedToHtml' == evt.data.action) {
                // Update TinyMCE content
                tinyMCE.get(evt.data.elementId).setContent(evt.data.text);
            }
        });

        // Plug submit listener on forms having TinyMCE fields
        $(document).ready(function() {
            $('form').each(function() {
                if (0 == $(this).find('.tinymce').length) {
                    return
                }

                $(this).on('submit', function() {
                    // If submit in progress, ignore
                    if (true == $(this).data('isSubmitPending')) {
                        return false;
                    }

                    $(this).data('isSubmitPending', true);
                    var tinyMceFields = $(this).find('.tinymce');
                    var parsedTinyMceFields = $(this).find('.tinymce.is-parsed');

                    // If all TinyMCE contents have been parsed, do submit
                    if(parsedTinyMceFields.length == tinyMceFields.length) {
                        $(this).data('isSubmitPending', true);
                        return true;
                    }

                    // Trigger parsing on each fields
                    tinyMceFields.each(function() {
                        // Do nothing if parsing is in process
                        if (undefined != $(this).data('isParsingPending')) {
                            return;
                        }

                        var content = tinyMCE.get($(this).attr('id')).getContent();
                        if (content) {
                            $(this).data('isParsingPending', true);
                            parent.postMessage({
                                action: 'MediaDeclinationTagParser.htmlTagsToText',
                                returnAction: 'tinymceContentParsed',
                                text: content,
                                elementId: $(this).attr('id')
                            }, '*');
                        }
                        else {
                            $(this).addClass('is-parsed');
                        }
                    });

                    $(this).data('isSubmitPending', false);
                    return false;
                });
            });
        });
    }
})();
