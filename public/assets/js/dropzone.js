Dropzone.autoDiscover = false;

$(function() {
  'use strict';

  $('.dropzone').dropzone({
    url: '/medias',
    paramName: 'files',
    uploadMultiple: true,
    init: function () {
      console.log('hello world');
    },
  });
});
