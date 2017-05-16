
(function (window, undefined) {


    function setCookie(a, b) {
        var c = new Date;
        c.setTime(c.getTime() + 24 * b * 60 * 60 * 1e3), document.cookie = a + "=" + b + ";expires=" + c.toUTCString()
    }

    function getCookie(a) {
        var b = document.cookie.match("(^|;) ?" + a + "=([^;]*)(;|$)");
        return b ? b[2] : null
    }

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

    var last_active = document.querySelector('#cont-nav .top-tog.active');
    delegate(document, "click", ".top-tog", function() {
        var id = this.getAttribute("id");
        this.classList.add("active");
        var last_id = last_active.getAttribute("id");
        last_active.classList.remove("active");
        document.querySelector(".content-body ." + last_id + ".loops").classList.remove("active");
        document.querySelector(".content-body ." + id + ".loops").classList.add("active");
        last_active = this;
    });

    var last_toggled = document.querySelector(".attr.middle #proc-main");
    var panel = document.querySelector(".attr.middle #repop");
    delegate(document, "click", ".loop-tog", function() {
        var id = this.getAttribute("data-id");
        last_toggled.style.display = "none";
        last_toggled = document.querySelector(".attr.middle #" + id);
        last_toggled.style.display = "block";

        var a = this.getAttribute("data-id");
        var b = this.getAttribute("data-class");
        var c = this.getAttribute("data-type");
        var d = this.getAttribute("data-function");
        var e = this.getAttribute("title");
        var f = this.getAttribute("data-file");
        var g = this.getAttribute("data-line");

        if ("proc-buffer" != a) {
            var h = "";
            if (b) {
                if (d) {
                    h += '<div class="keyword">Class: ' + b + '</span><span style="margin: 3px;">' + c + '</span><span class="char-null">' + d + '</span>';
                } else {
                    h += '<div class="keyword">Function: <span class="char-null">' + b + '</span>';
                }
                h += '<span class="">()</span></div>';
            } else {
                h += '<div class="keyword">Class: <span class="char-null">null</span></div>';
            }
            if (e) {
                e = e.replace(/\\/g, '<b class="char-object"> \\ </b>');
                h += '<div class="namespace">Namespace: ' + e + '</div>';
            } else {
                h += '<div class="namespace">Namespace: <span class="char-null">null</span></div>';
            }
            h += '<div class="file">File: <span class="">' + f + '</span>:<span class="char-integer">' + g + '</span></div>';
            panel.innerHTML = h;panel.style.display = "block";
        }
        else { panel.style.display = "none";}
    });

    document.getElementById(getCookie("BittrDebug_toggle_right")).nextElementSibling.style.display = 'block';
    delegate(document, "click", ".global .labeled", function() {
        setCookie("BittrDebug_toggle_right", this.getAttribute("id"));
        var x = this.nextElementSibling;
        if (x.style.display === 'none') {
            x.style.display = 'block';
        } else {
            x.style.display = 'none';
        }
    });

    delegate(document, "click", ".contents .right .global .listed .caret", function() {
        var m = this.nextElementSibling;
        if (m.style.display === 'none') {
            m.style.display = 'block';
        } else {
            m.style.display = 'none';
        }
    });



    /**
     * @class window.tinyscrollbar
     * @constructor
     * @param {Object} [$container] Element to attach scrollbar to.
     * @param {Object} options
     @param {String} [options.axis='y'] Vertical or horizontal scroller? ( x || y ).
     @param {Boolean} [options.wheel=true] Enable or disable the mousewheel.
     @param {Boolean} [options.wheelSpeed=40] How many pixels must the mousewheel scroll at a time.
     @param {Boolean} [options.wheelLock=true] Lock default window wheel scrolling when there is no more content to scroll.
     @param {Number} [options.touchLock=true] Lock default window touch scrolling when there is no more content to scroll.
     @param {Boolean|Number} [options.trackSize=false] Set the size of the scrollbar to auto(false) or a fixed number.
     @param {Boolean|Number} [options.thumbSize=false] Set the size of the thumb to auto(false) or a fixed number
     @param {Boolean} [options.thumbSizeMin=20] Minimum thumb size.
     */
    var tinyscrollbar = function($container, options) {
        return new Plugin($container, options);
    };

    if(typeof define == 'function' && define.amd) {
        define(function(){ return tinyscrollbar; });
    }
    else if(typeof module === 'object' && module.exports) {
        module.exports = tinyscrollbar;
    }
    else {
        window.tinyscrollbar = tinyscrollbar;
    }



})(window);

window.onload = function()
{
    // The plain javascript api is very similar to the jquery version with some exceptions.
    // There is no chaining like in the jquery api. So when you create a instance it
    // will return all methods and properties.
    //
    var $scrollbar = document.getElementById("proc-main")
        ,   scrollbar  = tinyscrollbar($scrollbar)
    ;
    // You can now call methods liks scrollbar.update() or scrollbar.contentPosition
    // Here is a example how you can bind the move event to the scrollbar container.
    //
    $scrollbar.addEventListener("move", function()
    {
        console.log(scrollbar.contentPosition);
    }, false);
}



