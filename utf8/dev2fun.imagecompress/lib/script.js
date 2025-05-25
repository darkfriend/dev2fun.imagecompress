/**
 * @author darkfriend <hi@darkfriend.ru>
 * @version 0.11.5
 */
function SendPropcess(step, type) {
    let objRequest = {
        AJAX_IC: 'Y',
        PAGEN_1: step,
    };
    let wrapId = 'compressAllStatus';
    switch (type) {
        case 'convert':
            objRequest.convert_all = 'Y';
            wrapId = 'convertAllStatus';
            break;
        default:
            objRequest.compress_all = 'Y';
    }
    BX.ajax({
        url: window.location.pathname + window.location.search,
        data: objRequest,
        method: 'POST',
        timeout: 600,
        dataType: 'json',
        cache: false,
        onsuccess: function (data) {
            BX(wrapId).innerHTML = data.html;
            data.step = parseInt(data.step);
            data.allStep = parseInt(data.allStep);
            if (
                data.step > 0
                && data.step <= data.allStep
                && !data.error
            ) {
                SendPropcess(data.step, type);
            } else {
                BX.closeWait(wrapId);
            }
            if (data.step > data.allStep || !(data.step > 0)) {
                window.location.href = window.location.pathname + '?process_result=Y&status=success';
            }
        },
        onfailure: function () {
            BX.closeWait(wrapId);
            BX(wrapId).innerHTML = 'Error!';
        }
    });
}

function SearchPictures() {
    let objRequest = {
        AJAX_IC: 'Y',
        action: 'searchPicture',
    };
    let wrapId = '';
    BX.showWait();
    BX.ajax({
        url: window.location.pathname + window.location.search,
        data: objRequest,
        method: 'POST',
        timeout: 600,
        dataType: 'json',
        cache: false,
        onsuccess: function (data) {
            BX.closeWait();
            if (data.success) {
                window.location.href = window.location.pathname + '?process_search_result=Y&status=success';
            } else {
                let err = data.msg ? data.msg : 'Unknow error!'
                BX(wrapId).innerHTML = 'Error! ' + err;
            }
        },
        onfailure: function () {
            BX.closeWait();
            BX(wrapId).innerHTML = 'Error!';
        }
    });
}