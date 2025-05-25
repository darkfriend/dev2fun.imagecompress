if(typeof addNewRow === "undefined") {
    function addNewRow(tableID, row_to_clone) {
        var tbl = document.getElementById(tableID);
        var cnt = tbl.rows.length;
        if (row_to_clone == null)
            row_to_clone = -2;
        var sHTML = tbl.rows[cnt + row_to_clone].cells[0].innerHTML;
        var oRow = tbl.insertRow(cnt + row_to_clone + 1);
        var oCell = oRow.insertCell(0);

        var s, e, n, p;
        p = 0;
        while (true) {
            s = sHTML.indexOf('[n', p);
            if (s < 0) break;
            e = sHTML.indexOf(']', s);
            if (e < 0) break;
            n = parseInt(sHTML.substr(s + 2, e - s));
            sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
            p = s + 1;
        }
        p = 0;
        while (true) {
            s = sHTML.indexOf('__n', p);
            if (s < 0) break;
            e = sHTML.indexOf('_', s + 2);
            if (e < 0) break;
            n = parseInt(sHTML.substr(s + 3, e - s));
            sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
            p = e + 1;
        }
        p = 0;
        while (true) {
            s = sHTML.indexOf('__N', p);
            if (s < 0) break;
            e = sHTML.indexOf('__', s + 2);
            if (e < 0) break;
            n = parseInt(sHTML.substr(s + 3, e - s));
            sHTML = sHTML.substr(0, s) + '__N' + (++n) + '__' + sHTML.substr(e + 2);
            p = e + 2;
        }
        p = 0;
        while (true) {
            s = sHTML.indexOf('xxn', p);
            if (s < 0) break;
            e = sHTML.indexOf('xx', s + 2);
            if (e < 0) break;
            n = parseInt(sHTML.substr(s + 3, e - s));
            sHTML = sHTML.substr(0, s) + 'xxn' + (++n) + 'xx' + sHTML.substr(e + 2);
            p = e + 2;
        }
        p = 0;
        while (true) {
            s = sHTML.indexOf('%5Bn', p);
            if (s < 0) break;
            e = sHTML.indexOf('%5D', s + 3);
            if (e < 0) break;
            n = parseInt(sHTML.substr(s + 4, e - s));
            sHTML = sHTML.substr(0, s) + '%5Bn' + (++n) + '%5D' + sHTML.substr(e + 3);
            p = e + 3;
        }
        oCell.innerHTML = sHTML;

        var patt = new RegExp("<" + "script" + ">[^\000]*?<" + "\/" + "script" + ">", "ig");
        var code = sHTML.match(patt);
        if (code) {
            for (var i = 0; i < code.length; i++) {
                if (code[i] != '') {
                    s = code[i].substring(8, code[i].length - 9);
                    jsUtils.EvalGlobal(s);
                }
            }
        }

        if (BX && BX.adminPanel) {
            BX.adminPanel.modifyFormElements(oRow);
            BX.onCustomEvent('onAdminTabsChange');
        }

        setTimeout(function () {
            var r = BX.findChildren(oCell, {tag: /^(input|select|textarea)$/i});
            if (r && r.length > 0) {
                for (var i = 0, l = r.length; i < l; i++) {
                    if (r[i].form && r[i].form.BXAUTOSAVE)
                        r[i].form.BXAUTOSAVE.RegisterInput(r[i]);
                    else
                        break;
                }
            }
        }, 10);
    }
}

if(typeof cacheDeleteActive === "undefined") {
    function cacheDeleteActive() {
        let objRequest = {
            AJAX_MODE : 'Y',
            AJAX_IC : 'Y',
            active : 'Y',
            action : 'cache-deleted-agent',
            sessid: BX.bitrix_sessid()
        };
        BX.showWait();
        BX.ajax({
            url: window.location.href,
            data : objRequest,
            method : 'POST',
            timeout : 600,
            dataType: 'json',
            cache: false,
            onsuccess: function(data) {
                BX.closeWait();
                window.location.reload();
            },
            onfailure: function(){
                BX.closeWait();
                alert('Error!');
            }
        });
    }
}

if(typeof cacheDeleteDeactivate === "undefined") {
    function cacheDeleteDeactivate() {
        let objRequest = {
            AJAX_MODE : 'Y',
            AJAX_IC : 'Y',
            active : 'N',
            action : 'cache-deleted-agent',
            sessid: BX.bitrix_sessid()
        };
        BX.showWait();
        BX.ajax({
            url: window.location.href,
            data : objRequest,
            method : 'POST',
            timeout : 600,
            dataType: 'json',
            cache: false,
            onsuccess: function(data) {
                BX.closeWait();
                window.location.reload();
            },
            onfailure: function(){
                BX.closeWait();
                alert('Error!');
            }
        });
    }
}

if(typeof compressAgentActive === "undefined") {
    function compressAgentActive(act) {
        let objRequest = {
            AJAX_MODE : 'Y',
            AJAX_IC : 'Y',
            active : 'Y',
            action : act,
            sessid: BX.bitrix_sessid()
        };
        BX.showWait();
        BX.ajax({
            url: window.location.href,
            data : objRequest,
            method : 'POST',
            timeout : 600,
            dataType: 'json',
            cache: false,
            onsuccess: function(data) {
                BX.closeWait();
                window.location.reload();
            },
            onfailure: function(){
                BX.closeWait();
                alert('Error!');
            }
        });
    }
}

if(typeof compressAgentDeactivate === "undefined") {
    function compressAgentDeactivate(act) {
        let objRequest = {
            AJAX_MODE : 'Y',
            AJAX_IC : 'Y',
            active : 'N',
            action : act,
            sessid: BX.bitrix_sessid()
        };
        BX.showWait();
        BX.ajax({
            url: window.location.href,
            data : objRequest,
            method : 'POST',
            timeout : 600,
            dataType: 'json',
            cache: false,
            onsuccess: function(data) {
                BX.closeWait();
                window.location.reload();
            },
            onfailure: function(){
                BX.closeWait();
                alert('Error!');
            }
        });
    }
}

if(typeof cacheClearAll === "undefined") {
    function cacheClearAll() {
        let objRequest = {
            AJAX_MODE : 'Y',
            AJAX_IC : 'Y',
            action : 'cache-clear-all',
            sessid: BX.bitrix_sessid()
        };
        BX.showWait();
        BX.ajax({
            url: window.location.href,
            data : objRequest,
            method : 'POST',
            timeout : 600,
            dataType: 'json',
            cache: false,
            onsuccess: function(data) {
                BX.closeWait();
                alert(data.message);
            },
            onfailure: function(){
                BX.closeWait();
                alert('Error!');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function(){
    const convertModeSelect = document.querySelectorAll('.select__convert_mode');
    if (convertModeSelect.length) {
        Array.prototype.forEach.call(convertModeSelect, function(opt) {
            opt.onchange = function (e) {
                let el = e.target;
                let hasLazy = false;
                Array.prototype.forEach.call(el.options, function(opt) {
                    if (opt.selected && opt.value === 'lazyConvert') {
                        hasLazy = true;
                        return undefined;
                    }
                });
                if (hasLazy) {
                    // toggleLazyConvertSettings(true);
                    Array.prototype.forEach.call(el.options, function(opt) {
                        if (opt.selected && opt.value !== 'lazyConvert') {
                            opt.selected = false;
                        }
                    });
                } //else {
                    // toggleLazyConvertSettings(false);
                //}
            };
        });

    }

    // if (document.querySelector(".convert__lazy_settings")) {
    //     Array.prototype.forEach.call(convertModeSelect.options, function(opt) {
    //         if (opt.selected && opt.value === 'lazyConvert') {
    //             toggleLazyConvertSettings(true);
    //             return undefined;
    //         }
    //     });
    // }


    // function toggleLazyConvertSettings(show) {
    //     Array.prototype.forEach.call(document.querySelectorAll(".convert__lazy_settings"), function(e) {
    //         if (show) {
    //             e.style.display = 'table-row';
    //         } else {
    //             e.style.display = 'none';
    //         }
    //     });
    // }
});
