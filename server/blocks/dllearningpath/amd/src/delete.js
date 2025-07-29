YUI().use('node-base', function (Y) {
    function init() {
        Y.all(".delete_learning_path").on('click', function (e) {
            var args = {
                'url': e.currentTarget.get('href'),
                'message':
                    e.currentTarget.get('title')
            };
            M.util.show_confirm_dialog(e, args);
            return false;
        });
    }

    Y.on("domready", init);


});