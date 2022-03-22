import axios from 'axios';

/**
 * Customized upload picture plugin
 */
class CKEditorUploadAdapter {
    constructor(loader, routeLink, destination) {
        this.loader = loader;
        this.destination = destination;
        this.routeLink = routeLink;
    }

    async upload() {
        const data = new FormData();
        data.append('file', await this.loader.file);
        data.append('destination', this.destination);

        const res = await axios({
            url: this.routeLink,
            method: 'POST',
            data,
            withCredentials: false, // True is not allowed to bring token, false is allowed
        });

        // Method Returns data format: {Default: "URL"}
        return {
            default: res.data.url,
        };
    }
}

export default CKEditorUploadAdapter;
