$(document).ready(function(){
    $(".global").click(function(){
        $(this).find('.content').slideToggle("fast");
    });
});
$(".right, .code-view").mCustomScrollbar({theme:"minimal-dark"});

var h = $('.code-view table').height();
h = 'calc(85vh - ' + h + 'px)';
h = 'height:-moz-' + h + ';height:-webkit-' + h + ';height:-o-' + h + ';height:' + h;
$('.code-view table .last-map').attr('style', h)

$('.content-nav .top-tog').click(function () {
    var clicked = $(this).attr('id');
    $('.content-nav .top-tog').removeClass('active');
    $(this).addClass('active');

    $('.content-body .loops div').removeClass('active');
    $('.content-body .loops .' + clicked).addClass('active');


})

