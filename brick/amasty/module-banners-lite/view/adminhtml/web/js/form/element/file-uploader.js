define([
    'Magento_Ui/js/form/element/file-uploader',
    'jquery',
], function (fileUploader, $) {
    return fileUploader.extend({
        defaults: {
            deleteUrl: null,
            fileInputName: 'banner_image'
        },

        /**
         * @param {Event} event - Event object.
         * @param {Object} data - File data that will be uploaded.
         */
        onBeforeFileUpload: function (event, data) {
            this._super();
            if (this.value().length !== 0) {
                this.removeFile(this.value()[0])
            }
        },

        /**
         * @param {Object} file - Data of the file that will be removed.
         */
        removeFile: function (file) {
            const formData = new FormData();
            let deleted = false;

            formData.append('form_key', $('[name="form_key"]').val());
            formData.append('banner_image', file.name);

            $.ajax({
                async: false,
                showLoader: true,
                url: this.deleteUrl,
                processData: false,
                contentType: false,
                data: formData,
                method: 'post',
                success: function (res) {
                    if (!res.error) {
                        deleted = true;
                    }
                }
            });

            if (deleted) {
                this._super();
            }

            return this;
        }
    })
});
