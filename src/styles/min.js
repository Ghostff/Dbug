var content_nav = document.getElementsByClassName("content-nav");
var content_nav_size = content_nav.length;
var top_tog;

var content_body = document.getElementsByClassName("content-body");
var content_body_size = content_body.length;
var loops;

var loop_div;

for (var i = 0; i < content_nav_size; i++) {
    top_tog = content_nav[i].getElementsByClassName("top-tog");
}
var top_tog_size = top_tog.length;

var myFunction = function() {
    for (var i = 0; i < top_tog_size; i++) {
        top_tog[i].classList.remove("active");
    }
    this.classList.add("active");
};

for (var i = 0; i < top_tog_size; i++) {
    top_tog[i].addEventListener('click', myFunction, false);
}

for (var i = 0; i < content_nav_size; i++) {
    content_nav[i].getElementsByClassName("top-tog");
}

for (var i = 0; i < content_body_size; i++) {
    loops = content_body[i].getElementsByClassName("loops");
}
var loops_size = loops.length;

for (var i = 0; i < loops_size; i++) {
    loop_div = content_body[i].getElementsByTagName("div");
}



