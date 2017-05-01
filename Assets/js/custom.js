$(document).ready(function(){
    $(".global").click(function(){
        $(this).find('.content').slideToggle("fast");
    });
});
$(".right, .code-view").mCustomScrollbar({axis:"yx",theme:"minimal-dark"});

$( "table" ).each(function( index ) {
    var h = $(this).height();
    h = 'calc(87.3vh - ' + h + 'px)';
    h = 'height:-moz-' + h + ';height:-webkit-' + h + ';height:-o-' + h + ';height:' + h;

});



$('.content-nav .top-tog').click(function () {
    var clicked = $(this).attr('id');

    $('.content-nav .top-tog').removeClass('active');
    $(this).addClass('active');

    $('.content-body .loops div').removeClass('active');
    $('.content-body .loops .' + clicked).addClass('active');
})
$('.code-view').css("visibility", "hidden");

$('.loop-tog').click(function() {
   var toggle = $(this).attr('data-id');
   $('.code-view').css("visibility", "hidden");
   $('#' + toggle).css("visibility", "visible");
});

