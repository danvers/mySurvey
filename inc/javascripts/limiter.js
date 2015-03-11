/**
 * Created by dan on 11.03.2015.
 */
$(document).ready(function() {
    $('textarea').bind('keypress keydown', function (e) {
        var tval = $(this).val(),
            tlength = tval.length,
            textlimit = 200,
            remain = parseInt(textlimit - tlength);
        $(this).css('border-color','#ccc');
        if(remain <= (textlimit /2)){
            $('#t_'+($(this).attr('id'))).text(remain);
        }else{
            $('#t_'+($(this).attr('id'))).html('&nbsp;');
        }
        if (remain <= 0 && e.which !== 0 && e.charCode !== 0) {
            $(this).val((tval).substring(0, tlength - 1));
            $(this).css('border-color', '#b1423e');
        }
    });
});