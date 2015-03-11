$(document).ready(function() {
    /**
     * set jqueryUI tooltips
     */
    $(".tooltip").tooltip();
    /*
    * toggle field types in edit mode
    */
    $('#toggle_switch').change(function() {
        $('.toggle').hide();
        $('.toggle :input').each(function(){ $(this).val(''); });
        $('.t-'+this.selectedIndex).toggle('slow');
    });
    /**
     * show input limits on textareas
     */
    $('textarea').bind('keypress keydown', function (e) {

        var textlimit = 200,
            tval = $(this).val(),
            tlength = tval.length;

        if($(this).attr('data-limit') !== undefined){
            textlimit = parseInt($(this).attr('data-limit'));
            if(textlimit == 0) return false;
        }
        var remain = parseInt(textlimit - tlength);

        $(this).css('border-color','#ccc');

        if(remain <= (textlimit / 2)){
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