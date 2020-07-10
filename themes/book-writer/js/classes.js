const cookies = class {
    static get all() {
        const _cookies_arr = document.cookie.split('; ');
        const _cookies = {};
        for (let i = 0; i < _cookies_arr.length; i++) {
            let split = _cookies_arr[i].split('=');
            _cookies[split[0]] = split[1];
        }
        return _cookies;
    }
    static set (name, value,{ _expires =  30, path = '/' } = {}) {
        const d = new Date();
        d.setTime(d.getTime() + (_expires*24*60*60*1000));
        const expires = "expires="+ d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=" + path;    
    }
}
const acs = class {
    static set (key, value) {
        const _acs = acs.all;
        _acs[key] = value;
        const _acs_array = Object.entries(_acs);
        const acs_string_array = [];
        for (let i = 0; i < _acs_array.length; i++) {
            acs_string_array.push( _acs_array[i].join('>') );
        }
        cookies.set('acs',acs_string_array.join('/'));
    }
    static get all() {
        const cookies_obj = cookies.all.acs;
        const acs_cookie = cookies_obj ? cookies_obj.split('/') : [];
        const acs_obj = {};
        for (let i = 0; i < acs_cookie.length; i++) {
            const cookie_arr = acs_cookie[i].split('>');
            acs_obj[cookie_arr[0]] = cookie_arr[1];
        }
        return acs_obj;
    }
}
const themes = class {
    static set (theme, {temp = false} = {}) {
        if (! themes.all[theme]){
            theme = 'light';
        }
        const root = document.documentElement;
        const themes_keys = Object.keys(themes.all[theme]);
        for (let i = 0; i < themes_keys.length; i++) {
            const theme_key = _.camelToHyphen(themes_keys[i]);
            root.style.setProperty("--" + theme_key, themes.all[theme][themes_keys[i]] );
        }
        if (! temp) {
            cookies.set("theme", theme );
        }
    }
    static get current () {
        return cookies.all.theme;
    }
}
themes.all = {
    light: {
        themeColor: "#007ACC",
        textColor: "#262828",
        backgroundColor: "#fdfdfd",
        backgroundAccent: "#ececec"
    },
    dark: {
        themeColor: "#265f86",
        textColor: "#b7bfc4",
        backgroundColor: "#121212",
        backgroundAccent: "#212020"
    },
    peach: {
        themeColor: "#007ACC",
        textColor: "#262828",
        backgroundColor: "#edd1b0",
        backgroundAccent: "#ececec"
    }
}
themes.set(themes.current);