export default class Media {
    static compressed(imageId, w, h, type = 'jpg') {
        return `/content/${imageId}/${w}/${h}/${type}`;
    }

    static empty(w, h, text = 'No image') {
        return `//placehold.it/${w}x${h}?text=${encodeURI(text)}`;
    }

    static video(code) {
        return `https://www.youtube.com/embed/${code}`;
    }
}