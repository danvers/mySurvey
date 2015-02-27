function showhide(myform, edit) {

    var el = myform.field_type.selectedIndex;

    if ((el + 1) == 1) {

        new Effect.Fade('div-dropdown', {duration: 0, from: 1, to: 0});
        new Effect.Fade('div-checkboxes', {duration: 0, from: 1, to: 0});
        new Effect.Appear('div-polar');

        if (!edit) {
            myform.minVal.value = '';
            myform.maxVal.value = '';
            myform.multiple.checked = '';
        }
    } else if ((el + 1) == 2) {
        new Effect.Fade('div-polar', {duration: 0, from: 1, to: 0});
        new Effect.Fade('div-dropdown', {duration: 0, from: 1, to: 0});

        new Effect.Appear('div-checkboxes');

        if (!edit) {
            myform.minVal.value = '';
            myform.maxVal.value = '';
            myform.multiple.checked = '';
        }
    } else if ((el + 1) == 4) {
        new Effect.Fade('div-polar', {duration: 0, from: 1, to: 0});
        new Effect.Fade('div-checkboxes', {duration: 0, from: 1, to: 0});

        new Effect.Appear('div-dropdown');
        if (!edit) {
            myform.minVal.value = '';
            myform.maxVal.value = '';
            myform.multiple.checked = '';
        }
    } else if ((el + 1) == 3) {
        new Effect.Fade('div-polar', {duration: 1, from: 1, to: 0});
        new Effect.Fade('div-dropdown', {duration: 1, from: 1, to: 0});
        new Effect.Fade('div-notes', {duration: 1, from: 1, to: 0});

        new Effect.Fade('div-checkboxes', {duration: 1, from: 1, to: 0});
        myform.notes.checked = '';

    } else {
        new Effect.Fade('div-polar', {duration: 1, from: 1, to: 0});
        new Effect.Fade('div-dropdown', {duration: 1, from: 1, to: 0});
        new Effect.Appear('div-notes');
        if (!edit) {
            myform.minVal.value = '';
            myform.maxVal.value = '';
            myform.multiple.checked = '';

        }
    }
}
function textLeft(ta_name, counter, maxNum) {
    var ta_field = $(ta_name);
    var cnt = $(counter);
    if (ta_field.value.length > maxNum) {
        ta_field.value = ta_field.value.substring(0, maxNum);

    } else if (ta_field.value.length > (maxNum / 2)) {
        cnt.innerHTML = '<strong style="color:red;">Hinweis</strong>:Du kannst noch ' + (maxNum - ta_field.value.length) + ' Zeichen eingeben';
    } else {
        cnt.innerHTML = '&nbsp;';
    }
}