window.addEvent('domready', function() {
    document.formvalidator.setHandler('length11',
        function (value) {
            if (value.length == 11) {
                return true;
            } else {
                return false;
            }
    });

    document.formvalidator.setHandler('length16',
        function (value) {
            if (value.length == 16) {
                return true;
            } else {
                return false;
            }
    });


});
