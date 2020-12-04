if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false;
}

function initWizardForProcess() {
    if (!$.fn.steps) return;

    const validRequiredInputs = ($section) => {
        let valid = true;

        const $inputs = $section.find('input[required]');

        $inputs.each((_, input) => {
            if (!$(input).val().trim()) {
                valid = false;
                if ($(input).parent().hasClass('input-group')) {
                    $(input).parent().parent().append(`
                        <label class="error ml-0 mt-2 text-danger">Vui lòng nhập thông tin này`
                    );
                } else {
                    $(input).parent().append(`
                        <label class="error ml-0 mt-2 text-danger">Vui lòng nhập thông tin này`
                    );
                }

            } else {
                $(input).next('label').remove();
            }
        });

        return valid;
    };

    const serializeObject = (serializeArray) => {
        const result = {};

        serializeArray.forEach((element) => {
            result[element.name] = element.value
        });

        return result;
    };

    const $processForm = $('#process-form');

    $processForm.steps({
        labels: {
            finish: 'Hoàn tất',
            next: 'Tiếp theo',
            previous: 'Quay lại',
        },
        headerTag: 'h2',
        bodyTag: 'section',
        transitionEffect: 'slideLeft',
        onStepChanging: function (event, currentIndex) {
            return validRequiredInputs($($processForm.find('section')[currentIndex]));
        },
        onFinishing: function (event, currentIndex) {
            return validRequiredInputs($($processForm.find('section')[currentIndex]));
        },
        onFinished: function (event, currentIndex) {
            $('#process-form a[href="#finish"]').attr('disabled', true).css({'padding': '.5rem 1rem .3rem'});
            $('#process-form a[href="#finish"]').html(`
                <span class="spinner-border text-light" style="width: 1rem; height: 1rem;"></span>
            `);
            const serializableData = $processForm.serializeArray();

            if (serializableData.length === 0 || !$processForm.hasClass('editable')) {
                return true;
            }

            $.ajax({
                url: '/processes/create',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json; charset=UTF-8',
                data: JSON.stringify({
                    _token: $('meta[name="_token"]').attr('content'),
                    ...serializeObject(serializableData)
                }),
                success: function (res) {
                    window.location.href = `/processes/${res.data.id}`;
                },
                error: function (res) {
                    $('#process-form a[href="#finish"]').attr('disabled', false).attr('style', null);
                    $('#process-form a[href="#finish"]').html('Hoàn tất');

                    Swal.fire({
                        icon: 'error',
                        title: 'Đã có lỗi xảy ra',
                        text: res.responseJSON.message,
                    });
                }
            });
        }
    });
}

function initDropzone() {
    if (!$.fn.dropzone || typeof Dropzone == 'undefined') return;
    if ($('.dropzone:not(.search-face__dropzone)').length === 0) return;

    const uploadImg = $('.dropzone:not(.search-face__dropzone)').data('type') === 'image';

    const dropzone = new Dropzone('.dropzone', {
        url: '/medias',
        paramName: 'files',
        uploadMultiple: uploadImg,
        autoProcessQueue: false,
        maxFilesize: 3000,
        timeout: 360000,
        // addRemoveLinks: true,
        // dictRemoveFile: 'Xóa hình',
        // dictCancelUpload: 'Huỷ',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
        },
        init: function () {
            this.on('success', function (res) {
                const body = JSON.parse(res.xhr.response);

                if (uploadImg) {
                    const startIndex = $('.image-links > div').length;

                    body.data.forEach((url, index) => {

                    });
                } else {
                    $('.dropzone-field').val(body.data[0]);
                }
            });
        },
    });

    $('.dropzone-submit').click(function (e) {
        e.preventDefault();
        dropzone.processQueue();
    });

    $('a[href="#collapseDropzone"]').click(function () {
        const $field = $('.dropzone-field');
        $field.attr('readonly', !$field.attr('readonly'));
    });
}

$(document).ready(function () {
    initWizardForProcess();
    initDropzone();
});
