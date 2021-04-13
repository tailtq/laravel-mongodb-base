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

function renderIdentity(matchingFaceIds, id) {
    return matchingFaceIds ? `
        <a href="${matchingFaceIds[0].original_url}" data-lightbox="object-${id}">
            <img src="${matchingFaceIds[0].original_url}" style="width: inherit; height: 60px;" alt="">
        </a>
    ` : ``;
}

function addZero(i) {
    if (i < 10) {
        i = '0' + i;
    }
    return i;
}

function renderBlock(object, appearances = []) {
    const avatar = object.avatars[0];

    return (`
        <tr data-id="${object._id}"
            data-track-id="${object.track_id}"
            data-identity-id="${object.identity?._id}"
            ${!object.identity && $('[name="hide-unknown"]').is(':checked') ? 'style="display: none"' : ''}>
            <td class="text-center">${object.track_id}</td>
            <td class="text-center">
                ${avatar ? `<img src="${avatar}" alt="image" style="width: inherit; height: 60px;">` : ''}
            </td>
            <td class="text-center">${renderIdentity(object.identity?.matching_face_ids, object.id)}</td>
            <td>${renderIdentityName(object.identity, object.confidence_rate)}</td>
            <td>
                ${buildTimeRanges(appearances)}
            </td>
            <td width="50px" class="text-center">
                <a href="#"
                   data-video-result="${object.video_result || ''}"
                   style="display: ${(globalStatus === 'done' || globalStatus === 'stopped') ? 'inline' : 'none'}"
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

function renderObjectWithIdentity($element, object) {
    $element.attr('data-identity-id', object.identity?._id).removeAttr('style');
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
                rendering_duration: renderingDuration,
                total_duration: totalDuration,
            } = res.data;

            $('.process__detecting-duration').html(detectingDuration);
            $('.process__rendering-duration').html(renderingDuration);
            $('.process__total-duration').html(totalDuration);
        },
    });
}

Echo.channel(`process.${processId}.objects`).listen('.Modules\\Process\\Events\\ObjectsAppear', (res) => {
    $('.socket__message').remove();

    res.data.forEach((object) => {
        if (trackIds.indexOf(object.track_id) >= 0) {
            const $element = $(`.socket-render tbody tr[data-track-id="${object.track_id}"]`);

            if (object.to_frame) {
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

Echo.channel(`process.${processId}.cluster`).listen('.Modules\\Process\\Events\\ClusteringProceeded', (res) => {
    console.log(res);

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

Echo.channel(`process.${processId}.analysis`).listen('.Modules\\Process\\Events\\AnalysisProceeded', (res) => {
    console.log(res.data.statistic);
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

Echo.channel(`process.${processId}.progress`).listen('.Modules\\Process\\Events\\ProgressChange', (res) => {
    console.log(res.data);
    const {
        status,
        progress,
        video_result: videoResult,
    } = res.data;

    globalStatus = status;

    if (allStatus[status]) {
        const $processStatus = $('.process__status');
        $processStatus.text(allStatus[status]);

        if (status === 'error' || status === 'stopped') {
            $processStatus.removeClass('badge-success').addClass('badge-danger');
        }
    }
    if (status === 'done' || status === 'stopped') {
        if (status === 'done') {
            Toast.fire({
                type: 'success',
                title: 'Tiến trình đã hoàn thành',
            });
        }
        $('.btn-stop').attr('disabled', true);
        $('.export-statistic__btn').removeAttr('disabled');
        $('.render-video__btn').removeAttr('disabled');
        $('.render-single-object').removeAttr('style');
        flvPlayer.destroy();
        renderTime();
    } else if (status === 'detecting' || status === 'rendering') {
        const $element = $(`.progress-bar__${status}`);

        $element.css({width: `${progress}%`});
        $element.attr('aria-valuenow', progress);
        $element.text(`${progress}%`);
    } else if (status === 'error') {
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

Echo.channel(`process.${processId}.objects`).listen('.Modules\\Process\\Events\\ObjectVideoRendered', function (res) {
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

$(document).ready(function () {
    renderData();
    listenObjectRenderingEvent();
});
