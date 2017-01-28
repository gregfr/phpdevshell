TestCase('Test URL', {
    'test URL encode valid params':   function ()
    {
        assertEquals({}, PHPDS.url.decodeParams('http://localhost/toto'));
        assertEquals({}, PHPDS.url.decodeParams('http://localhost/toto?'));
        assertEquals({a: 1}, PHPDS.url.decodeParams('http://localhost/toto?a=1'));
        assertEquals({a: 1, b: 2}, PHPDS.url.decodeParams('http://localhost/toto?a=1&b=2'));
        assertEquals({a: 2}, PHPDS.url.decodeParams('http://localhost/toto?a=1&a=2'));
    },
    'test URL encode invalid params': function ()
    {
        assertEquals({}, PHPDS.url.decodeParams('http://localhost/toto&'));
        assertEquals({}, PHPDS.url.decodeParams('http://localhost/toto&a=1'));
    },
    'test URL decode valid params': function ()
    {
        assertEquals('http://localhost/toto?a=1&b=2', PHPDS.url.encodeParams({a: 1, b: 2}, 'http://localhost/toto'));
        assertEquals('http://localhost/toto?a=1&b=2', PHPDS.url.encodeParams({a: 1, b: 2}, 'http://localhost/toto?'));
        assertEquals('http://localhost/toto?a=1&b=2', PHPDS.url.encodeParams({a: 1, b: 2}, 'http://localhost/toto?a=1'));
        assertEquals('http://localhost/toto?a=1&b=2', PHPDS.url.encodeParams({a: 1, b: 2}, 'http://localhost/toto?a=1&b=2'));
        assertEquals('http://localhost/toto?a=1&b=2', PHPDS.url.encodeParams({a: 1, b: 2}, 'http://localhost/toto?a=2'));
        assertEquals('http://localhost/toto?a=1&b=2', PHPDS.url.encodeParams({a: 1, b: 2}, 'http://localhost/toto?a=2&b=1'));

        assertEquals('http://localhost/toto?a=1', PHPDS.url.encodeParams({a: 1}, 'http://localhost/toto?a=1'));
        assertEquals('http://localhost/toto?a=1&b=2', PHPDS.url.encodeParams({a: 1}, 'http://localhost/toto?a=1&b=2'));
        assertEquals('http://localhost/toto?a=3&b=2', PHPDS.url.encodeParams({a: 3}, 'http://localhost/toto?a=1&b=2'));
    }
});

TestCase('PHPDS', {
    'test error handler installation': function ()
    {
//        var old_handler = window.onerror;
//        PHPDS.errorHandler();
//        assertEquals(PHPDS.errorHandler, window.onerror);
//        assertFunction()


    }
});

AsyncTestCase('Test RemoteCall', {
    'test RemoteCall time out': function (queue)
    {
        $.mockjaxClear();
        $.mockjax({
            url:          '/test',
            type:         'POST',
            responseTime: 500,
            isTimeout:    true
        });

        queue.call('Testing for time out behavior', function (callbacks)
        {

            var onReturn = callbacks.add(function (ajax)
            {
                assertEquals('0', ajax.status);
            });

            PHPDS.remoteCallURL = '/test';
            $.when(PHPDS.remoteCall('toto'))
                .fail(function (ex)
                {
                    onReturn(ex.ajax);
                })
                .done(function (result, state, ajax)
                {
                    onReturn(ajax)
                })
            ;
        });

    },

    'test RemoteCall wrong function name': function (queue)
    {
        $.mockjaxClear();
        $.mockjax({
            url:          '/test',
            type:         'POST',
            responseTime: 500,
            status:       501
        });

        queue.call('Testing wrong remote function name', function (callbacks)
        {

            var onReturn = callbacks.add(function (ajax)
            {
                assertEquals('501', ajax.status);
            });

            PHPDS.remoteCallURL = '/test';
            $.when(PHPDS.remoteCall('function'))
                .fail(function (ex)
                {
                    onReturn(ex.ajax);
                })
                .done(function (result, state, ajax)
                {
                    onReturn(ajax)
                })
            ;
        });

    }

    /*testRemoteCall1: function(queue) {
     $.mockjax({
     url: '/test',
     type: 'POST',
     responseTime: 500,
     responseText: {
     status: 'success',
     fortune: 'yes'
     }
     });

     queue.call('Step 1: send the remote call', function(callbacks) {

     var onReturn = callbacks.add(function(ajax) {
     assertEquals('201', ajax.status);
     });

     //      PHPDS.errorHandler(onFail);

     PHPDS.remoteCallURL = '/test';
     $.when(PHPDS.remoteCall('toto'))
     //$.when($.ajax('/test2', {}))
     .fail(function(ex) {
     onReturn(ex.ajax);
     })
     .done(function(result, state, ajax) {
     onReturn(ajax)
     })
     ;
     });

     *//*queue.call('Step 2: assert the response body matches what we expect', function() {
     assertEquals('hello', responseBody);
     });*//*
     }*/
});
