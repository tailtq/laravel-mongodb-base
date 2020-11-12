Dropzone.autoDiscover = false;

$(function () {
  'use strict';

  $('.dropzone').dropzone({
    url: '/medias',
    paramName: 'files',
    headers: {
      _token: $('meta[name="_token"]').attr('content'),
    },
    uploadMultiple: true,
    init: function () {
      console.log('hello world');
    },
  });
});

Dropzone.options.imageUpload = {
  // maxFilesize: 1,
  acceptedFiles: ".jpeg,.jpg,.png,.gif"
};