function timestamp_duration(timestamp){
    var hours = Math.floor(timestamp / 60 / 60);
    var minutes = Math.floor(timestamp / 60) - (hours * 60);  
    var seconds = timestamp % 60;
  
    var formatted = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    return formatted;
}
function api(action,{data,reCAPTCHA = null} = {}){
    return new Promise((res,rej) => {
        const submission = {
            action,
            placeholder: DOM.q('placeholder_data').innerText
        };
        if (reCAPTCHA === null){
            submission.nonce = DOM.q('nonce').innerHTML;
        }
        else{
            submission.reCAPTCHA = reCAPTCHA;
        }
        if (typeof data === 'object'){
            submission.data = data;
        }
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api');
        xhr.responseType = "json";
        xhr.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        xhr.setRequestHeader("Accept", "application/json, text/javascript, */*; q=0.01");
        xhr.send(JSON.stringify(submission));
        xhr.onload = () => {
            if (xhr.getResponseHeader("x-is-serving-offline") === "true") {
                window.is_online = false;
                return;
            }
            window.is_online = true;
            if (xhr.status !== 200) {
                rej(xhr.response);
                return;
            }
            if (xhr.response.code === 993) {
                window.location.reload();
            }
            res(xhr.response);
        };
        xhr.onerror = () => {
            window.is_online = false;
            rej({code: 1000});
        };
    });
}