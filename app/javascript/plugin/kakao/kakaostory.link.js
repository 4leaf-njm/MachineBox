/*
 Copyright 2012 kakaostory
 */


(function (window, undefined) {
    var kakaostorynew = {};
    window.kakaostorynew = window.kakaostorynew || kakaostorynew;


    var uagentnew = navigator.userAgent.toLocaleLowerCase();
    if (uagentnew.search("android") > -1) {
        kakaostorynew.os = "android";
        if (uagentnew.search("chrome") > -1) {
            kakaostorynew.browser = "android+chrome";
        }
    } else if (uagentnew.search("iphone") > -1 || uagentnew.search("ipod") > -1 || uagentnew.search("ipad") > -1) {
        kakaostorynew.os = "ios";
    }


    var kakaostorynewapp = {
        talk: {
            base_url: "kakaolink://sendurl?",
            apiver: "2.0.1",
            store: {
                android: "market://details?id=com.kakao.talk",
                ios: "http://itunes.apple.com/app/id362057947"
            },
            package: "com.kakao.talk"
        },
        story: {
            base_url: "storylink://posting?",
            apiver: "1.0",
            store: {
                android: "market://details?id=com.kakao.story",
                ios: "http://itunes.apple.com/app/id486244601"
            },
            package: "com.kakao.story"
        }
    };


    kakaostorynew.link = function (name) {
        var link_app = kakaostorynewapp[name];
        if (!link_app) return { send: function () {
            throw "No App exists";
        }};
        return {
            send: function (params) {
                var _app = this.kakaostorynewapp;
                params['apiver'] = _app.apiver;
                var full_url = _app.base_url + serializednew(params);


                var install_block = (function (os) {
                    return function () {
                        window.location = _app.store[os];
                    };
                })(this.os);


                if (this.os == "ios") {
                    var timer = setTimeout(install_block, 2 * 1000);
                    window.addEventListener('pagehide', clearTimernew(timer));
                    window.location = full_url;
                } else if (this.os == "android") {
                    if (this.browser == "android+chrome") {
                        window.location = "intent:" + full_url + "#Intent;package=" + _app.package + ";end;";
                    } else {
                        var iframe = document.createElement('iframe');
                        iframe.style.display = 'none';
                        iframe.src = full_url;
                        iframe.onload = install_block;
                        document.body.appendChild(iframe);
                    }
                }
            },
            kakaostorynewapp: link_app,
            os: kakaostorynew.os,
            browser: kakaostorynew.browser
        };


        function serializednew(params) {
            var stripped = [];
            for (var k in params) {
                if (params.hasOwnProperty(k)) {
                    stripped.push(k + "=" + encodeURIComponent(params[k]));
                }
            }
            return stripped.join("&");
        }


        function clearTimernew(timer) {
            return function () {
                clearTimeout(timer);
                window.removeEventListener('pagehide', arguments.callee);
            };
        }
    };
}(window));

