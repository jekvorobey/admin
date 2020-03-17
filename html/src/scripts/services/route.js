import qs from 'qs';

export default class RouteService {
    get(key, def = null) {
        let query = qs.parse(document.location.search.substr(1));
        if (query.hasOwnProperty(key)) {
            return query[key];
        }

        return def;
    }

    push(params, path = null) {
        path = path || location.pathname;
        window.history.pushState(null, null, location.origin + path + '?' + qs.stringify(params, {
            arrayFormat: "brackets",
            encode: false
        }));
    }
}
