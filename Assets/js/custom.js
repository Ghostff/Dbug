$(document).ready(function(){
    $(".global").click(function(){
        $(this).children('.content').slideToggle("fast");
    });
});
$(".right, .code-view").mCustomScrollbar({axis:"yx",theme:"minimal-dark"});

function heightloop(id) {
    var table = $('#' + id + ' table');
    var h = table.height();
    h = 'calc(87.3vh - ' + h + 'px)';
    h = 'height:-moz-' + h + ';height:-webkit-' + h + ';height:-o-' + h + ';height:' + h;
    table.find('.last-map').attr('style', h);
}
$('.content-nav .top-tog').click(function () {
    var clicked = $(this).attr('id');

    $('.content-nav .top-tog').removeClass('active');
    $(this).addClass('active');

    $('.content-body .loops div').removeClass('active');
    $('.content-body .loops .' + clicked).addClass('active');
})


$('.loop-tog').click(function() {
    $('.code-view table .last-map').attr('style', '');
   var toggle = $(this).attr('data-id');
   $('.code-view, .browser-view').hide()
   $('#' + toggle).show();
    heightloop(toggle);
});

$('.exception-type .action span').click(function () {
    window.open($(this).attr('url'), '_blank');
})

heightloop('proc-main');


