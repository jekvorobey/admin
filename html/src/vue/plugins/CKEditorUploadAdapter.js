import axios from 'axios';

/**
 * Customized upload picture plugin
 */
class CKEditorUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    async upload() {
        const data = new FormData();
        data.append('file', await this.loader.file);

        const res = await axios({
            url: `${process.env.VUE_APP_BASE_URL}/upload`,
            method: 'POST',
            data,
            withCredentials: false, // True is not allowed to bring token, false is allowed
        });

        // Method Returns data format: {Default: "URL"}
        return {
            default: process.env.VUE_APP_TARGET_URL + res.data.data.url,
        };
    }
}

export default CKEditorUploadAdapter;
