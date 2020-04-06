import Media from "../../scripts/media";

export default {
    methods: {
        imageUrl(id, w, h, type = 'jpg') {
            return id ? Media.compressed(id, w, h, type) : Media.empty(w, h);
        },
        fileUrl(id) {
            return Media.file(id)
        }
    }
}