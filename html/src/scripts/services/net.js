import axios from 'axios';
import qs from 'qs';
import Services from "./services";

function paramSerialise(params) {
    // Qs is already included in the Axios package
    return qs.stringify(params, {
        arrayFormat: "brackets",
        encode: false
    });
}

export default class NetService {
    static prepareUri(uri, params) {
        let re = /\{([\w\-]+)\}/g;
        let match = re.exec(uri);
        let keys = new Set();
        while (match) {
            keys.add(match[1]);
            match = re.exec(uri);
        }
        for (let key of keys) {
            uri = uri.replace(new RegExp(`\{${key}\}`), params[key]);
            delete params[key];
        }
        return {uri, params};
    }

    params(method, uri, params = {}, data = {}, options={}) {
        let store = Services.store();
        let {uri: newUri, params: newParams} = NetService.prepareUri(uri, params);
        let config = Object.assign({
            url: newUri,
            method: method,
            params: newParams,
            // mode: 'cors',
            // withCredentials: false,
            headers: {
                'Accept': 'application/json',
            },
            paramsSerializer: paramSerialise
        }, options);
        if (['post', 'put'].includes(method)) {
            if (data instanceof FormData) {
                config.data = data;
            } else {
                config.headers['Content-Type'] = 'application/json';
                config.data = JSON.stringify(data);
            }
        }

        if (store.state.env.token) {
            config.headers['Authorization'] = `Bearer ${store.state.env.token}`;
        }

        return config;
    }

    get(uri, params = {}, options={}) {
        return this.request(this.params('get', uri, params, {}, options));
    }

    post(uri, params = {}, data = {}, options={}) {
        return this.request(this.params('post', uri, params, data, options))

    }

    put(uri, params = {}, data = {}, options={}) {
        return this.request(this.params('put', uri, params, data, options));
    }

    delete(uri, params = {}, options={}) {
        return this.request(this.params('delete', uri, params, {}, options));
    }

    request(params) {
        return axios.request(params).then(resp => {
            if (resp.status >= 200 && resp.status < 300) {
                return resp.data;
            }
        }).catch(error => {
            if (error.response) {
                // The request was made and the server responded with a status code
                // that falls out of the range of 2xx
                console.log(error.response.data);
                console.log(error.response.status);
                console.log(error.response.headers);
            } else if (error.request) {
                // The request was made but no response was received
                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                // http.ClientRequest in node.js
                console.log(error.request);
            } else {
                // Something happened in setting up the request that triggered an Error
                console.log('Error', error.message);
            }
            console.log(error.config);

            return Promise.reject();
        });
    }
}
