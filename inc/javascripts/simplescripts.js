$(document).ready(function() {
    $('#toggle_switch').change(function() {
        $('.toggle').hide();
        $('.toggle :input').each(function(){ $(this).val(''); });
        $('.t-'+this.selectedIndex).toggle('slow');
    });
    $(".tooltip").tooltip();
});