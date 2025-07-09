var config = {
    config: {
        mixins: {
            'Magento_Theme/js/view/messages': {
                'Magento_Theme/js/view/messages-mixin': true
            }
        }
    },
    paths: {
        'matchHeight': "js/matchheight",
    },
    shim: {
        'matchHeight':  {
            'deps': ['jquery']
        },
    }
};
