/**
 * Created by darkfriend
 * @version 0.2.1
 */
function SendPropcess(step) {
    BX.ajax({
        url: window.location.pathname,
        data : {
            AJAX_IC : 'Y',
            PAGEN_1 : step,
            compress_all : "Y"
        },
        method : 'POST',
        timeout : 600,
        dataType: 'json',
        cache: false,
        onsuccess: function(data) {
            BX('compressAllStatus').innerHTML = data.html;
            data.step = parseInt(data.step);
            data.allStep = parseInt(data.allStep);
            if(data.step>0&&data.step<=data.allStep&&!data.error) {
                SendPropcess(data.step);
            } else {
                BX.closeWait('compressAllStatus');
            }
            if(data.step>data.allStep) {
                window.location.href = window.location.pathname+'?compress_result=Y&status=success';
            }
        },
        onfailure: function(){
            BX.closeWait('compressAllStatus');
			BX('compressAllStatus').innerHTML = 'Error!';
        }
    });
}
BX.ready(function(){
    BX.showWait('compressAllStatus');
    SendPropcess(1);
});