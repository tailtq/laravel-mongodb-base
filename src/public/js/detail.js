let currentFrame = 0;
let trackIds = [];

// global variables are defined at detail.blade.php

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 2000,
});

// function for alert message when click action play, stop
function processMessage(type) {
    if (type === 'start') {
        Toast.fire({
            type: 'success',
            title: 'Bắt đầu thực thi'
        });
    } else {
        Toast.fire({
            type: 'success',
            title: 'Kết thúc thực thi'
        });
    }
}

function reloadIcons() {
    feather.replace();
    $('[data-toggle="popover"]').popover();
}

function sendStartStopRequest(processId, type) {
    $.ajax({
        url: `/processes/${type}-process`,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify({
            _token: $('meta[name="_token"]').attr('content'),
            processId: processId
        }),
        success: function (res) {
            if (type === 'start') {
                $('.btn-start').attr('disabled', true);
                $('.btn-stop').attr('disabled', false);
            } else if (type === 'stop') {
                $('.btn-stop').attr('disabled', true);
            }
            processMessage(type);
        },
        error: function ({responseJSON: res}) {
            Toast.fire({
                type: 'error',
                title: res.message
            });
        }
    });
}

$('.btn-start').click(function () {
    sendStartStopRequest(processId, 'start');
    flvPlayer.load();
    flvPlayer.play();
});

$('.btn-stop').click(function () {
    sendStartStopRequest(processId, 'stop');
});

$('.render-video__btn').click(function (e) {
    const href = $(this).data('href');

    if (href) {
        window.open(href, '_blank');
        return;
    }
    e.preventDefault();
    $.ajax({
        url: `/processes/render-video`,
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json; charset=UTF-8',
        data: JSON.stringify({
            _token: $('meta[name="_token"]').attr('content'),
            processId: processId
        }),
        success: function (res) {
            console.log(res);
            Toast.fire({
                type: 'success',
                title: res.data
            });
        },
        error: function ({responseJSON: res}) {
            Toast.fire({
                type: 'error',
                title: res.message
            });
        }
    });
});

function getTimeString(frameFrom, frameTo, fps, renderHour) {
    let secondFrom = Math.floor(frameFrom / fps);
    let minFrom = Math.floor(secondFrom / 60);
    const hourFrom = (Math.floor(minFrom / 60)).toString().padStart(2, '0');
    minFrom = (minFrom % 60).toString().padStart(2, '0');
    secondFrom = (secondFrom % 60).toString().padStart(2, '0');

    if (!Number.isInteger(frameTo)) {
        return `${renderHour ? `${hourFrom}:` : ''}${minFrom}:${secondFrom} - now`;
    }

    let secondTo = Math.floor(frameTo / fps);
    let minTo = Math.floor(secondTo / 60);
    const hourTo = (Math.floor(minTo / 60)).toString().padStart(2, '0');
    minTo = (minTo % 60).toString().padStart(2, '0');
    secondTo = (secondTo % 60).toString().padStart(2, '0');

    return `${renderHour ? `${hourFrom}:` : ''}${minFrom}:${secondFrom} - ${renderHour ? `${hourTo}:` : ''}${minTo}:${secondTo}`;
}

function renderIdentity(images, id) {
    images = images ? JSON.parse(images) : null;

    return images ? `
        <a href="${images[0].url}" data-lightbox="object-${id}">
            <img src="${images[0].url}" style="width: inherit; height: 60px;" alt="">
        </a>
    ` : ``;
}

function addZero(i) {
    if (i < 10) {
        i = '0' + i;
    }
    return i;
}

function buildTimeRanges(appearances) {
    let timeRanges = '';

    appearances.forEach((value) => {
        const clusteringType = `<p class="text-center">Id: ${value.track_id} ${value.clustering_type ? ` - ${value.clustering_type}` : ''}</p>`;
        let imageHTML = '';
        let bodyHTML = '';
        let time = '';

        if (isRealtime) {
            const timeFrom = new Date(value.time_from);
            const timeTo = new Date(value.time_from);
            time += `${addZero(timeFrom.getHours())}:${addZero(timeFrom.getMinutes())}:${addZero(timeFrom.getSeconds())}`;
            time += ` - ${addZero(timeTo.getHours())}:${addZero(timeTo.getMinutes())}:${addZero(timeTo.getSeconds())}`;
        } else {
            time = getTimeString(value.frame_from, value.frame_to, fps, renderHour);
        }

        value.images = JSON.parse(value.images);
        value.body_images = value.body_images ? JSON.parse(value.body_images) : [];

        value.images.forEach((image) => {
            imageHTML += `<img class="original-avatar" src="${image}" alt="">`;
        });
        value.body_images.forEach((body) => {
            bodyHTML += `<img class="original-body" src="${body}" alt="">`;
        });
        // TODO: Generate HTML and CSS content
        timeRanges += `
            <button class="badge badge-info"
                    role="button"
                    data-html="true"
                    data-toggle="popover"
                    data-placement="top"
                    data-id="${value.id}"
                    data-track-id="${value.track_id}"
                    data-identity-id="${value.identity_id}"
                    data-cluster-id="${value.cluster_id}"
                    data-mongo-id="${value.mongo_id}"
                    data-content='<div>${clusteringType}<div class="text-center">${imageHTML}</div><div class="text-center">${bodyHTML}</div></div>'
                    data-trigger="focus">${time}</button> &nbsp;
        `;
    });

    return timeRanges;
}

function renderBlock(object, appearances = []) {
    const avatar = JSON.parse(object.images)[0];

    return (`
        <tr data-id="${object.id}"
            data-track-id="${object.track_id}"
            data-identity-id="${object.identity_id}"
            data-cluster-id="${object.cluster_id}"
            data-mongo-id="${object.mongo_id}"
            ${!object.identity_id && $('[name="hide-unknown"]').is(':checked') ? 'style="display: none"' : ''}>
            <td class="text-center">${object.track_id}</td>
            <td class="text-center">
                ${avatar ? `<img src="${avatar}" alt="image" style="width: inherit; height: 60px;">` : ''}
            </td>
            <td class="text-center">${renderIdentity(object.identity_images, object.id)}</td>
            <td>${renderIdentityName(object.identity_name, object.confidence_rate) || 'Không xác định'}</td>
            <td>
                ${buildTimeRanges(appearances)}
            </td>
            <td width="50px" class="text-center">
                <a href="#"
                   data-video-result="${object.video_result || ''}"
                   style="display: ${globalStatus === 'done' ? 'inline' : 'none'}"
                   class="render-single-object icon__normal-font-size ${object.video_result ? 'text-success' : 'text-secondary'}">
                    ${object.video_result ? `<i class="mdi mdi-play"></i>` : '<i class="mdi mdi-video-switch"></i>'}
                </a>
            </td>
        </tr>
    `);
}

function renderBlockInOrder(html, order, trackIds) {
    let index;

    if (order !== 0) {
        index = order - 1;
    }
    if (order === 0) {
        $('.socket-render tbody').prepend(html);
    } else {
        const prevTrackId = trackIds[index];
        const $element = $(`.socket-render tbody tr[data-track-id="${prevTrackId}"]`);

        if ($element.length === 0) {
            $('.socket-render tbody').append(html);
        } else {
            $element.after(html);
        }
    }
}

function renderIdentityName(identityName, confidenceRate) {
    if (!identityName) {
        return null;
    }
    return `${identityName} (${confidenceRate}%)`;
}

function renderObjectWithIdentity($element, object) {
    $element.attr('data-identity-id', object.identity_id).removeAttr('style');
    $element.find('td:nth-child(3)').html(renderIdentity(object.identity_images, object.id, object.confidence_rate));
    $element.find('td:nth-child(4)').text(renderIdentityName(object.identity_name, object.confidence_rate));
    $element.find('td:nth-child(6)').html(`
        <a href="#"
           data-video-result=""
           style="display: ${globalStatus === 'done' ? 'inline' : 'none'}"
           class="render-single-object icon__normal-font-size text-secondary">
            <i class="mdi mdi-video-switch"></i>
        </a>
    `);
}

function insertInOrder(element, array) {
    array.push(element);
    array.sort(function (a, b) {
        return a - b;
    });

    return [array, array.indexOf(element)];
}

function renderData() {
    $.ajax({
        url: `/processes/${processId}/objects`,
        type: 'GET',
        success: function (res) {
            if (res.data.length > 0) {
                $('.socket__message').remove();
            }

            res.data.forEach((value) => {
                if (trackIds.indexOf(value.track_id) >= 0) {
                    return;
                }
                [trackIds, trackIndex] = insertInOrder(value.track_id, trackIds);

                renderBlockInOrder(
                    renderBlock(value, value.appearances),
                    trackIndex,
                    trackIds
                );
            });
            reloadIcons();
        },
    });
}

function renderTime() {
    $.ajax({
        url: `/processes/${processId}/detail`,
        type: 'GET',
        success: function (res) {
            const {
                detecting_duration: detectingDuration,
                // matching_duration: matchingDuration,
                rendering_duration: renderingDuration,
                total_duration: totalDuration,
            } = res.data;

            $('.process__detecting-duration').html(detectingDuration);
            // $('.process__matching-duration').html(matchingDuration);
            $('.process__rendering-duration').html(renderingDuration);
            $('.process__total-duration').html(totalDuration);
        },
    });
}

Echo.channel(`process.${processId}.objects`).listen('.App\\Events\\ObjectsAppear', (res) => {
    $('.socket__message').remove();

    res.data.forEach((object) => {
        if (trackIds.indexOf(object.track_id) >= 0) {
            const $element = $(`.socket-render tbody tr[data-track-id="${object.track_id}"]`);

            if (object.frame_to) {
                $(`.socket-render tbody tr[data-track-id="${object.track_id}"] td:nth-child(5)`).html(
                    buildTimeRanges([object])
                );
            }
            if (object.identity_name) {
                renderObjectWithIdentity($element, object);
            }
        } else {
            [trackIds, trackIndex] = insertInOrder(object.track_id, trackIds);

            renderBlockInOrder(
                renderBlock(object, [object]),
                trackIndex,
                trackIds
            );
        }
    });
    reloadIcons();
});

Echo.channel(`process.${processId}.cluster`).listen('.App\\Events\\ClusteringProceeded', (res) => {
    res.data.grouped_objects.forEach((object) => {
        const $element = $(`.socket-render tbody tr[data-track-id="${object.track_id}"]`);

        if (!object.identity_id && object.identity_name) {
            renderObjectWithIdentity($element, object);
        }
        $element.find('td:nth-child(5)').html(buildTimeRanges(object.appearances));
        object.appearances.forEach((appearance) => {
            if (appearance.id !== object.id) {
                $(`.socket-render tbody tr[data-track-id="${appearance.track_id}"]`).addClass('d-none');
            }
        });
    });
    reloadIcons();

    const {
        total_appearances: totalAppearances,
        total_objects: totalObjects,
        total_identified: totalIdentified,
        total_unidentified: totalUnidentified,
    } = res.data.statistic;

    $('td.statistic__total-appearances').html(totalAppearances);
    $('td.statistic__total-objects').html(totalObjects);
    $('td.statistic__total-identified').html(totalIdentified);
    $('td.statistic__total-unidentified').html(totalUnidentified);
});

Echo.channel(`process.${processId}.analysis`).listen('.App\\Events\\AnalysisProceeded', (res) => {
    const {
        total_appearances: totalAppearances,
        total_objects: totalObjects,
        total_identified: totalIdentified,
        total_unidentified: totalUnidentified,
    } = res.data;

    $('td.statistic__total-appearances').html(totalAppearances);
    $('td.statistic__total-objects').html(totalObjects);
    $('td.statistic__total-identified').html(totalIdentified);
    $('td.statistic__total-unidentified').html(totalUnidentified);
});

Echo.channel(`process.${processId}.progress`).listen('.App\\Events\\ProgressChange', (res) => {
    console.log(res.data);
    const {
        status,
        progress,
        video_result: videoResult,
        frame_index: frameIndex,
    } = res.data;

    globalStatus = status;

    if (!isNaN(frameIndex)) {
        currentFrame = frameIndex;
    }
    if (allStatus[status]) {
        const $processStatus = $('.process__status');
        $processStatus.text(allStatus[status]);

        if (status === 'error' || status === 'stopped') {
            $processStatus.removeClass('badge-success').addClass('badge-danger');
        }
    }
    if (status === 'done') {
        Toast.fire({
            type: 'success',
            title: 'Tiến trình đã hoàn thành',
        });
        flvPlayer.destroy();

        $('.search-face__btn').removeAttr('disabled');
        $('.export-statistic__btn').removeAttr('disabled');
        $('.render-video__btn').removeAttr('disabled');
        $('.render-single-object').removeAttr('style');
        renderTime();
    } else if (status === 'detecting' || status === 'rendering') {
        const $element = $(`.progress-bar__${status}`);

        $element.css({width: `${progress}%`});
        $element.attr('aria-valuenow', progress);
        $element.text(`${progress}%`);
    } else if (status === 'error' || status === 'stopped') {
        flvPlayer.destroy();
    } else if (status === 'rendered') {
        $('.render-video__btn')
            .addClass('btn-success')
            .removeClass('btn-secondary')
            .data('href', videoResult);

        Toast.fire({
            type: 'success',
            title: 'Tổng hợp video hoàn tất',
        });
    }
});

function listenObjectRenderingEvent() {
    $(document).on('click', '.render-single-object', function (e) {
        e.preventDefault();
        const videoResult = $(this).data('video-result');

        if (videoResult) {
            $('#videoModal').modal('show');
            $('#videoModal video').attr('src', videoResult);
            return;
        }
        const id = $(this).parent().closest('tr').data('id') || $(this).parent().closest('li').data('id');

        $(this).addClass('disabled');
        $(this).html(`<i class="icon__normal-size" data-feather="rotate-cw"></i>`);
        feather.replace();
        $(this).find('svg').addClass('infinite-spin');

        $.post(`/objects/${id}/rendering`, {
            _token: $('meta[name="_token"]').attr('content'),
        }).then((res) => {
            // TODO: add spinning icon
            console.log(res);
        });
    });
}

Echo.channel(`process.${processId}.objects`).listen('.App\\Events\\ObjectVideoRendered', function (res) {
    const { data } = res;

    $(`.socket-render tbody tr[data-id="${data.id}"] td:last-child a`)
        .html('<i class="mdi mdi-play"></i>')
        .addClass('text-success')
        .removeClass('disabled')
        .blur()
        .data('video-result', data.video_result);
    $(`.search-face__result li[data-id="${data.id}"] a.render-single-object`)
        .html('<i class="mdi mdi-play"></i>')
        .addClass('text-success')
        .removeClass('disabled')
        .blur()
        .data('video-result', data.video_result);
    feather.replace();
});

$('[name="hide-unknown"]').on('change', function () {
    const shouldHide = $(this).is(':checked');
    const $null = $(`.socket-render tbody tr[data-identity-id="null"]`);
    const $undefined = $(`.socket-render tbody tr[data-identity-id="undefined"]`);

    if (shouldHide) {
        $null.fadeOut(1000);
        $undefined.fadeOut(1000);
    } else {
        $null.fadeIn(1000);
        $undefined.fadeIn(1000);
    }
});

function initSearchFace() {
    if (!$.fn.dropzone || typeof Dropzone == 'undefined') return;

    const dropzone = new Dropzone('.search-face__dropzone', {
        url: '/processes/search-faces',
        paramName: 'file',
        uploadMultiple: false,
        autoProcessQueue: false,
        maxFilesize: 3000,
        timeout: 360000,
        addRemoveLinks: true,
        dictRemoveFile: 'Xóa hình',
        dictCancelUpload: 'Huỷ',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
        },
        sending: function (file, xhr, formData) {
            addSpinningIcon($('.dropzone-submit'), true);
            formData.append('process_ids', JSON.stringify([$('.process').data('id')]));
            formData.append('search_type', $('[name="search_type"]:checked').val());
        },
        success: function (res) {
            const { data } = JSON.parse(res.xhr.response);

            $('.search-face__result').removeAttr('style');
            $('.search-face__result .list-unstyled').html('');

            if (data.length === 0) {
                $('.search-face__result .list-unstyled').html(`<li>Không có đối tượng nào được tìm thấy</li>`);
                return;
            }
            data.forEach((element) => {
                let appearance = '';
                element.appearances.forEach((element) => {
                    appearance += `
                        <span class="badge badge-primary mr-1">
                            ${getTimeString(element.frame_from, element.frame_to, fps, renderHour)}
                        </span>`;
                });
                element.images = JSON.parse(element.images);

                $('.search-face__result .list-unstyled').append(`
                    <li class="media d-block d-sm-flex mb-3 align-items-center" data-id="${element.id}">
                        <img src="${element.images[0]}" class="mb-3 mb-sm-0 mr-3 img-fluid" style="height: 80px;">

                        <div class="media-body">
                            <p class="mt-0 mb-1">
                                <b>${renderIdentityName(element.identity_name, element.confidence_rate) || 'Không xác định'}</b>
                            </p>
                            <div>
                                <p>Thời gian xuất hiện:</p>
                                ${appearance}
                            </div>
                        </div>
                        
                        <div>
                            <a href="#"
                               data-video-result="${element.video_result || ''}"
                               class="render-single-object icon__normal-font-size ${element.video_result ? 'text-success' : 'text-secondary'}">
                                ${element.video_result ? `<i class="mdi mdi-play"></i>` : '<i class="mdi mdi-video-switch"></i>'}
                            </a>
                        </div>
                    </li>      
                `);
            });
        },
        complete: function () {
            $('.dropzone-submit').attr('disabled', false).text('Tìm kiếm');
        },
    });

    $('.dropzone-submit').click(function (e) {
        e.preventDefault();
        dropzone.processQueue();
    });
}

function addSpinningIcon(self, isBtn) {
    if (isBtn) {
        $(self).attr('disabled', true);
    } else {
        $(self).addClass('disabled');
    }

    $(self).html(`<i class="icon__normal-size" data-feather="rotate-cw"></i>`);
    feather.replace();
    $(self).find('svg').addClass('infinite-spin');
}

$(document).ready(function () {
    renderData();
    listenObjectRenderingEvent();
    initSearchFace();
});
