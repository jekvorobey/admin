import axios from 'axios';
import Services from "./services";

export default class NetService {
    static prepareUri(uri, params) {
        let re = /:([\w\-]+)/g;
        let match = re.exec(uri);
        let keys = new Set();
        while (match) {
            keys.add(match[1]);
            match = re.exec(uri);
        }
        for (let key of keys) {
            uri = uri.replace(new RegExp(`:${key}`), params[key]);
            delete params[key];
        }
        return {uri, params};
    }

    params(method, uri, params = {}, data = {}) {
        let store = Services.store();
        let {uri: newUri, params: newParams} = NetService.prepareUri(uri, params);
        let config = {
            url: newUri,
            method,
            newParams,
            data,
            mode: 'cors',
            withCredentials: false,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
        };

        if (store.state.env.token) {
            config.headers['Authorization'] = `Bearer ${store.state.env.token}`;
        }

        return config;
    }

    get(uri, params = {}) {
        return axios.request(this.params('get', uri, params));
    }

    post(uri, data = {}) {
        return axios.request(this.params('post', uri, {}, data));
    }

    put(uri, params = {}, data = {}) {
        return axios.request(this.params('put', uri, params, data));
    }

    delete(uri, params = {}) {
        return axios.request(this.params('delete', uri, params));
    }
}
