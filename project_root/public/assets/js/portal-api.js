(function (window) {
    'use strict';

    function apiBase() {
        var base = window.__PORTAL_API_BASE__ || '';
        return base.replace(/\/$/, '');
    }

    function apiPath(path) {
        path = path.charAt(0) === '/' ? path : '/' + path;
        return apiBase() + path;
    }

    async function request(method, path, body) {
        var opts = {
            method: method,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };
        if (body !== undefined) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        var res = await fetch(apiPath(path), opts);
        var data = {};
        try {
            data = await res.json();
        } catch (e) {
            data = {};
        }
        if (!res.ok) {
            var err = new Error(data.error || data.message || ('HTTP ' + res.status));
            err.status = res.status;
            err.payload = data;
            throw err;
        }
        return data;
    }

    window.PortalApi = {
        get: function (path) { return request('GET', path); },
        post: function (path, body) { return request('POST', path, body); },
        put: function (path, body) { return request('PUT', path, body); },
        delete: function (path) { return request('DELETE', path); }
    };
})(window);
