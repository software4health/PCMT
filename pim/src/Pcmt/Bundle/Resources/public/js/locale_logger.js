define(
    ['pim/user-context'],   // array of dependencies we want to use
    function (UserContext) {    // callback that will be called each time this module is requested
        return {
            logLocale: function () {
                console.log(UserContext.get('uiLocale'));
            }
        };
    }
);