
(function (w, d) {
    function setCookie(a, b) {
        var c = new Date;
        c.setTime(c.getTime() + 24 * b * 60 * 60 * 1e3), document.cookie = a + "=" + b + ";expires=" + c.toUTCString()
    }

    function getCookie(a) {
        var b = document.cookie.match("(^|;) ?" + a + "=([^;]*)(;|$)");
        return b ? b[2] : null
    }


    var raf = w.requestAnimationFrame || w.setImmediate || function(c) { return setTimeout(c, 0); };

    function initEl(el, mg) {
        if (el.hasOwnProperty('data-simple-scrollbar')) return;
        Object.defineProperty(el, 'data-simple-scrollbar', new SimpleScrollbar(el, mg));
    }

    // Mouse drag handler
    function dragDealer(el, context) {
        var lastPageY;

        el.addEventListener('mousedown', function(e) {
            lastPageY = e.pageY;
            el.classList.add('__BittrDebuger__ss-grabbed');
            d.body.classList.add('__BittrDebuger__ss-grabbed');

            d.addEventListener('mousemove', drag);
            d.addEventListener('mouseup', stop);

            return false;
        });

        function drag(e) {
            var delta = e.pageY - lastPageY;
            lastPageY = e.pageY;

            raf(function() {
                context.el.scrollTop += delta / context.scrollRatio;
            });
        }

        function stop() {
            el.classList.remove('__BittrDebuger__ss-grabbed');
            d.body.classList.remove('__BittrDebuger__ss-grabbed');
            d.removeEventListener('mousemove', drag);
            d.removeEventListener('mouseup', stop);
        }
    }

    // Constructor
    function ss(el, mg) {
        this.target = el;
        this.mg = mg;

        this.bar = '<div class="__BittrDebuger__ss-scroll">';

        this.wrapper = d.createElement('div');
        this.wrapper.setAttribute('class', '__BittrDebuger__ss-wrapper');

        this.el = d.createElement('div');
        this.el.setAttribute('class', '__BittrDebuger__ss-content');

        this.wrapper.appendChild(this.el);

        while (this.target.firstChild) {
            this.el.appendChild(this.target.firstChild);
        }
        this.target.appendChild(this.wrapper);

        this.target.insertAdjacentHTML('beforeend', this.bar);
        this.bar = this.target.lastChild;

        dragDealer(this.bar, this);
        this.moveBar();

        this.el.addEventListener('scroll', this.moveBar.bind(this));
        this.el.addEventListener('mouseenter', this.moveBar.bind(this));

        this.target.classList.add('__BittrDebuger__ss-container');

        var css = window.getComputedStyle(el);
        if (css['height'] === '0px' && css['max-height'] !== '0px') {
            el.style.height = css['max-height'];
        }
    }

    ss.prototype = {
        moveBar: function(e) {
            var totalHeight = this.el.scrollHeight,
                ownHeight = this.el.clientHeight,
                _this = this;
            this.scrollRatio = ownHeight / totalHeight;
            raf(function() {
                if(_this.scrollRatio >= 1) {
                    _this.bar.classList.add('__BittrDebuger__ss-hidden')
                } else {
                    _this.bar.classList.remove('__BittrDebuger__ss-hidden')
                    _this.bar.style.cssText = 'height:' + (_this.scrollRatio) * 100 + '%; top:' + (_this.el.scrollTop / totalHeight ) * 100 + '%;right:-' + (_this.target.clientWidth - _this.bar.clientWidth - _this.mg) + 'px;';
                }
            });
        }
    }

    function initAll() {
        var nodes = d.querySelectorAll('*[__BittrDebuger__ss-container]');

        for (var i = 0; i < nodes.length; i++) {
            initEl(nodes[i]);
        }
    }

    d.addEventListener('DOMContentLoaded', initAll);
    ss.initEl = initEl;
    ss.initAll = initAll;

    w.SimpleScrollbar = ss;


    function getPos(el) {
        for (var lx=0, ly=0;
             el != null;
             lx += el.offsetLeft, ly += el.offsetTop, el = el.offsetParent);
        return {x: lx,y: ly};
    }

    var merg = getPos(document.querySelector('.__BittrDebuger__middle .__BittrDebuger__exception-type')).y +
               getPos(document.querySelector('.__BittrDebuger__middle .__BittrDebuger__exception-msg')).y;
    var size = getPos(document.getElementById('repop'));
    document.querySelectorAll('.__BittrDebuger__code-view').forEach(function(e) {
        var styled = e.getAttribute('style');
        e.setAttribute("style", "height:" + (size.y - merg + 8)  + "px;" + (styled ? styled : ''));
        SimpleScrollbar.initEl(e, 3);
    });

    document.querySelectorAll('.__BittrDebuger__global .__BittrDebuger__content').forEach(function(e) {
        var styled = e.getAttribute('style');
        e.style.display = 'block';
        var inner = e.clientHeight;
        e.style.display = 'none';
        e.setAttribute("style", "height:" + ((inner > 500) ? 600 : inner)  + "px;" + (styled ? styled : ''));
        SimpleScrollbar.initEl(e, 16);
    });

    var height = 35 + getPos(document.querySelector('.__BittrDebuger__left .__BittrDebuger__content-nav')).y;
    document.querySelectorAll('.__BittrDebuger__left .__BittrDebuger__loops').forEach(function(e) {
        e.setAttribute("style", "width: 102%;height: calc(100vh - " + height + "px);");
        SimpleScrollbar.initEl(e, 13);
    });

    function delegate(el, evt, sel, handler) {
        el.addEventListener(evt, function(event) {
            var t = event.target;
            while (t && t !== this) {
                if (t.matches(sel)) {
                    handler.call(t, event);
                }
                t = t.parentNode;
            }
        });
    }


    var last_active = document.querySelector('#cont-nav .__BittrDebuger__top-tog.__BittrDebuger__active');
    delegate(document, "click", ".__BittrDebuger__top-tog", function() {
        var id = this.getAttribute("id");
        this.classList.add("__BittrDebuger__active");
        var last_id = last_active.getAttribute("id");

        console.log(id, last_id);
        last_active.classList.remove("__BittrDebuger__active");
        document.querySelector(".__BittrDebuger__content-body ." + last_id + ".__BittrDebuger__loops").classList.remove("__BittrDebuger__active");
        document.querySelector(".__BittrDebuger__content-body ." + id + ".__BittrDebuger__loops").classList.add("__BittrDebuger__active");
        last_active = this;
    });

    var _cache;
    function _atg(_lt) {

        if (typeof _cache != "undefined") {
            _cache.setAttribute("style", "height:0px;");
        }
        var lh = _lt.clientHeight;
        var tbl = _lt.querySelector('table');
        var tbl_h = tbl.clientHeight;
        if (tbl_h == lh) {
            return;
        }

        if (tbl_h < lh) {
            var lmap = tbl.querySelector('.last-map');
            lmap.setAttribute("style", "height:" + (lh - tbl_h) + "px;");
        }
        _cache = lmap;
    }

    var last_toggled = document.querySelector(".__BittrDebuger__attr.__BittrDebuger__middle #proc-main");
    var panel = document.querySelector(".__BittrDebuger__attr.__BittrDebuger__middle #repop");

    _atg(last_toggled);

    delegate(document, "click", ".__BittrDebuger__loop-tog", function() {
        var id = this.getAttribute("data-id");
        last_toggled.style.display = "none";
        last_toggled = document.querySelector(".__BittrDebuger__attr.__BittrDebuger__middle #" + id);
        last_toggled.style.display = "block";

        var a = this.getAttribute("data-id");
        var b = this.getAttribute("data-class");
        var c = this.getAttribute("data-type");
        var d = this.getAttribute("data-function");
        var e = this.getAttribute("title");
        var f = this.getAttribute("data-file");
        var g = this.getAttribute("data-line");

        if ("proc-buffer" != a) {
            _atg(last_toggled);
            var h = "";
            if (b) {
                if (d) {
                    h += '<div class="__BittrDebuger__keyword">Class: ' + b + '</span><span style="margin: 3px;">' + c + '</span><span class="__BittrDebuger__char-null">' + d + '</span>';
                } else {
                    h += '<div class="__BittrDebuger__keyword">Function: <span class="__BittrDebuger__char-null">' + b + '</span>';
                }
                h += '<span>()</span></div>';
            } else {
                h += '<div class="__BittrDebuger__keyword">Class: <span class="__BittrDebuger__char-null">null</span></div>';
            }
            if (e) {
                e = e.replace(/\\/g, '<b class="__BittrDebuger__char-object"> \\ </b>');
                h += '<div class="__BittrDebuger__namespace">Namespace: ' + e + '</div>';
            } else {
                h += '<div class="__BittrDebuger__namespace">Namespace: <span class="__BittrDebuger__char-null">null</span></div>';
            }
            h += '<div class="__BittrDebuger__file">File: <span class="">' + f + '</span>:<span class="__BittrDebuger__char-integer">' + g + '</span></div>';
            panel.innerHTML = h;
            panel.style.display = "block";
        }
        else { panel.style.display = "none";}
    });


    var last_cookie = getCookie("BittrDebug_toggle_right");
    if (last_cookie) {
        document.getElementById(last_cookie).nextElementSibling.style.display = 'block';
    }
    delegate(document, "click", ".__BittrDebuger__global .__BittrDebuger__labeled", function() {
        setCookie("BittrDebug_toggle_right", this.getAttribute("id"));
        var x = this.nextElementSibling;
        if (x.style.display === 'none') {
            x.style.display = 'block';
        } else {
            x.style.display = 'none';
        }
    });


    delegate(document, "click", ".__BittrDebuger__contents .__BittrDebuger__right .__BittrDebuger__global .__BittrDebuger__listed .__BittrDebuger__caret", function() {
        var m = this.nextElementSibling;
        if (m.style.display === 'none') {
            m.style.display = 'block';
        } else {
            m.style.display = 'none';
        }
    });

    var u = document.querySelector(".__BittrDebuger__left .__BittrDebuger__content-body").clientWidth;
    document.querySelectorAll(".__BittrDebuger__content-body .__BittrDebuger__l-parent").forEach(function(a) {
        a.setAttribute("style", "width:" + (u - 5) + "px;")
    })


})(window, document);










