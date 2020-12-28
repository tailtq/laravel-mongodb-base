if (typeof Dropzone !== 'undefined') {
    Dropzone.autoDiscover = false;
}

function addSpinning($element) {
    $element
        .attr('disabled', true)
        .css({'padding': '.5rem 1rem .3rem'})
        .html(`<span class="spinner-border text-light" style="width: 1rem; height: 1rem;"></span>`);
}

function removeSpinning($element, text = 'Tiếp theo') {
    $element.attr('disabled', false).attr('style', null);
    $element.html(text);
}

function serializeObject(serializeArray) {
    const result = {};

    serializeArray.forEach((element) => {
        result[element.name] = element.value
    });

    return result;
}

function validateRegions(shapes) {
    let isValid = true;

    shapes.forEach((shape) => {
        Object.keys(shape).forEach((key) => {
            const { length } = shape[key].coordinates[0];

            if (length !== 0 && length < 3) {
                isValid = false;
                $('.canvas__error-message').text('Số lượng toạ độ của vùng không đủ (>= 3)');
            }
        });
    });

    return isValid;
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
                        <label class="error ml-0 mt-2 text-danger">Vui lòng nhập thông tin này</label>`
                    );
                } else {
                    $(input).parent().append(`
                        <label class="error ml-0 mt-2 text-danger">Vui lòng nhập thông tin này</label>`
                    );
                }
            } else {
                $(input).next('label').remove();
            }
        });

        return valid;
    };

    const $processForm = $('#process-form');
    let isAsyncStep = false;
    let loadedCanvas = false;

    const steps = $processForm.steps({
        labels: {
            finish: 'Hoàn tất',
            next: 'Tiếp theo',
            previous: 'Quay lại',
        },
        headerTag: 'h2',
        bodyTag: 'section',
        transitionEffect: 'slideLeft',
        onStepChanged: function (event, currentIndex) {
            if (currentIndex === 2 && !loadedCanvas) {
                loadCanvas();
                loadedCanvas = true;
            }
        },
        onStepChanging: function (event, currentIndex) {
            if (isAsyncStep) {
                isAsyncStep = false;
                return true;
            }
            if (!validRequiredInputs($($processForm.find('section')[currentIndex]))) {
                return false;
            }
            if (currentIndex === 0 && $processForm.hasClass('editable')) {
                const $nextBtn = $('#process-form a[href="#next"]');
                addSpinning($nextBtn);

                // Get thumbnail for drawing recognition boundary
                $.ajax({
                    url: '/processes/thumbnails',
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/json; charset=UTF-8',
                    data: JSON.stringify({
                        _token: $('meta[name="_token"]').attr('content'),
                        video_url: $processForm.find('[name="video_url"]').val(),
                    }),
                    success: function (res) {
                        const { thumbnail } = res.data;
                        removeSpinning($nextBtn);
                        $processForm.find('input[name="thumbnail"]').val(thumbnail);
                        $('#canvas-img').attr('src', thumbnail);

                        isAsyncStep = true;
                        $processForm.steps('next');
                    },
                    error: function (res) {
                        removeSpinning($nextBtn);

                        $processForm.find('input[name="thumbnail"]').parent().parent().append(
                            `<label class="error ml-0 mt-2 text-danger">Không lấy được ảnh, vui lòng thử lại</label>`
                        );
                    }
                });
            } else {
                return true;
            }
        },
        onFinishing: function (event, currentIndex) {
            return validRequiredInputs($($processForm.find('section')[currentIndex])) && validateRegions(regions);
        },
        onFinished: function (event, currentIndex) {
            const serializableData = $processForm.serializeArray();

            if (serializableData.length === 0 || !$processForm.hasClass('editable')) {
                $(this).parent().closest('.modal').modal('hide');
                setTimeout(() => {
                    steps.steps('previous');
                }, 500);

                return true;
            }
            const $finishBtn = $('#process-form a[href="#finish"]');
            addSpinning($finishBtn);

            const data = serializeObject(serializableData);
            $.ajax({
                url: '/processes/create',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json; charset=UTF-8',
                data: JSON.stringify({
                    _token: $('meta[name="_token"]').attr('content'),
                    ...data,
                    regions: data.regions ? JSON.parse(data.regions) : [],
                }),
                success: function (res) {
                    window.location.href = `/processes/${res.data.id}`;
                },
                error: function (res) {
                    removeSpinning($finishBtn, 'Hoàn tất');

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
                        $('.image-links').append(`
                            <div>
                                <input type="hidden" name="images[${startIndex + index}][url]" value="${url}">
                            </div>
                        `);
                        $('.images-visualization').append(`
                            <div class="col-md-4 mb-2">
                                <img src="${url}" alt="" class="img-fluid">
                            </div>
                        `);
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
