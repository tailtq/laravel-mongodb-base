Dropzone.autoDiscover = false;

$(function() {
  'use strict';

  $('.dropzone').dropzone({
    url: '/identities/create',
    paramName: 'files',
    headers: {
      'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    uploadMultiple: true,
    init: function () {
      console.log('hello world');
    },
  });
});

// Dropzone.options.imageUpload = {
//   maxFilesize         :       1,
//   acceptedFiles: ".jpeg,.jpg,.png,.gif"
// };