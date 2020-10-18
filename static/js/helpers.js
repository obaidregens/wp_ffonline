function timestamp_duration(timestamp){
    var hours = Math.floor(timestamp / 60 / 60);
    var minutes = Math.floor(timestamp / 60) - (hours * 60);  
    var seconds = timestamp % 60;
  
    var formatted = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    return formatted;
  }
function api(action,{data,callback,async = true,reCAPTCHA = null,reject} = {}){
    return new Promise((res,rej) => {
        const options = {
            dataType: 'JSON',
            url: '/api',
            type: 'post',
            data: {
                action,
                placeholder: document.querySelector('placeholder_data').innerText
            },
            async
        };
        if (reCAPTCHA === null){
            options.data.nonce = document.querySelector('nonce').innerHTML;
        }
        else{
            options.data.reCAPTCHA = reCAPTCHA;
        }
        if (typeof data === 'object'){
            options.data.data = data;
        }
        options.success = response => {
            window.is_online = true;
            if (response.code === 993) {
                window.location.reload();
            }
            res(response);
            if (callback instanceof Function){
                callback(response);
            }
        };
        options.error = response => {
            window.is_online = false;
            rej(response);
            if (reject instanceof Function){
                reject(response);
            }    
        };
        jQuery.ajax(options);
    });
}
