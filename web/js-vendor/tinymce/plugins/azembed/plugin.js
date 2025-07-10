/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-07-23 11:55:04
 */

tinymce.PluginManager.add('azembed', function(editor, url) {
    editor.addButton('azembed', {
        title : 'Embed HTML',
        icon : 'code',
        //text: 'Embed HTML',
        onclick : function() {
            editor.windowManager.open({
                title: 'Embed HTML',
                width: 500,
                height: 200,
                body: [
                    {type: 'textbox', multiline: true, rows: 10, cols: 20, name: 'html', label: 'Embed HTML'},
                ],
                onsubmit: function(event) {
                    editor.insertContent(event.data.html);
                }
            });
        }
    });
});
