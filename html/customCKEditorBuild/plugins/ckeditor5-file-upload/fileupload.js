import { Plugin } from 'ckeditor5/src/core';
import FileUploadEditing from './src/fileuploadediting';
import FileUploadUI from './src/fileuploadui';

export default class FileUpload extends Plugin {
    static get requires() {
        return [FileUploadEditing, FileUploadUI];
    }

    /**
     * @inheritDoc
     */
    static get pluginName() {
        return 'fileUpload';
    }
}
