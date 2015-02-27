<?php
function db_prepare_input($string)
{
    if (is_string($string)) {
        return trim(sanitize_string(stripslashes($string)));
    } elseif (is_array($string)) {
        reset($string);
        while (list($key, $value) = each($string)) {
            $string[$key] = db_prepare_input($value);
        }
        return $string;
    } else {
        return $string;
    }
}

function sanitize_string($string)
{
    $string = preg_replace('/ +/', ' ', trim($string));

    return preg_replace("/[<>]/", '_', $string);
}

function parse_input_field_data($data, $parse)
{
    return strtr(trim($data), $parse);
}

function output_string($string, $translate = false, $protected = false)
{
    if ($protected == true) {
        return htmlspecialchars($string);
    } else {
        if ($translate == false) {
            return parse_input_field_data($string, array('"' => '&quot;'));
        } else {
            return parse_input_field_data($string, $translate);
        }
    }
}

function output_string_protected($string)
{
    return output_string($string, false, true);
}

function draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true)
{

    global $_GET, $_POST;

    $field = '<input type="' . output_string($type) . '" name="' . output_string($name) . '"';

    if (($reinsert_value == true) && ((isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])))) {

        if (isset($_GET[$name]) && is_string($_GET[$name])) {
            $value = output_string_protected(stripslashes($_GET[$name]));
        } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
            $value = output_string_protected(stripslashes($_POST[$name]));
        }
    }
    if (strlen($value)) {
        $field .= ' value="' . output_string_protected($value) . '"';
    }


    if (strlen($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
}

function draw_password_field($name, $value = '', $parameters = 'maxlength="40"')
{
    return draw_input_field($name, $value, $parameters, 'password', false);
}

function draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '')
{

    global $_GET, $_POST;

    $selection = '<input type="' . output_string($type) . '" name="' . output_string($name) . '"';

    if (strlen($value)) $selection .= ' value="' . output_string($value) . '"';

    if (($checked == true) || (isset($_GET[$name]) && is_string($_GET[$name]) && (($_GET[$name] == 'on') || (stripslashes($_GET[$name]) == $value))) || (isset($_POST[$name]) && is_string($_POST[$name]) && (($_POST[$name] == 'on') || (stripslashes($_POST[$name]) == $value)))) {
        $selection .= ' checked="checked"';
    }

    if (strlen($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
}

function draw_checkbox_field($name, $value = '', $checked = false, $parameters = '')
{
    return draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
}

function draw_radio_field($name, $value = '', $checked = false, $parameters = '')
{
    return draw_selection_field($name, 'radio', $value, $checked, $parameters);
}

function draw_pulldown_menu($name, $values, $default = '', $parameters = '')
{

    global $_GET, $_POST;

    $field = '<select name="' . output_string($name) . '"';

    if (!empty($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && ((isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])))) {
        if (isset($_GET[$name]) && is_string($_GET[$name])) {
            $default = stripslashes($_GET[$name]);
        } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
            $default = stripslashes($_POST[$name]);
        }
    }

    for ($i = 0, $n = sizeof($values); $i < $n; $i++) {
        $field .= '<option value="' . output_string($values[$i]['id']) . '"';
        if ($default == $values[$i]['id']) {
            $field .= ' selected="selected"';
        }
        $field .= '>' . output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>' . "\n\t\t";
    }
    $field .= '</select>';


    return $field;
}

function draw_textarea_field($name, $width, $height, $text = '', $parameters = '', $reinsert_value = true)
{

    global $_GET, $_POST;

    $field = '<textarea name="' . output_string($name) . '"  cols="' . output_string($width) . '" rows="' . output_string($height) . '"';

    if (strlen($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (($reinsert_value == true) && ((isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name])))) {
        if (isset($_GET[$name]) && is_string($_GET[$name])) {
            $field .= output_string_protected(stripslashes($_GET[$name]));
        } elseif (isset($_POST[$name]) && is_string($_POST[$name])) {
            $field .= output_string_protected(stripslashes($_POST[$name]));
        }
    } elseif (strlen($text)) {
        $field .= $text;
    }

    $field .= '</textarea>';

    return $field;
}

function draw_hidden_field($name, $value = '', $parameters = '')
{

    global $_GET, $_POST;

    $field = '<input type="hidden" name="' . output_string($name) . '"';

    if (strlen($value)) {
        $field .= ' value="' . output_string($value) . '"';
    } elseif ((isset($_GET[$name]) && is_string($_GET[$name])) || (isset($_POST[$name]) && is_string($_POST[$name]))) {
        if ((isset($_GET[$name]) && is_string($_GET[$name]))) {
            $field .= ' value="' . output_string(stripslashes($_GET[$name])) . '"';
        } elseif ((isset($_POST[$name]) && is_string($_POST[$name]))) {
            $field .= ' value="' . output_string(stripslashes($_POST[$name])) . '"';
        }
    }

    if (strlen($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
}

function draw_polar_fields($name, $min = '', $max = '', $parameters = '', $size = 5)
{
    $fieldset = $min . ' ';

    for ($i = 0; $i < $size; $i++) {
        $fieldset .= draw_radio_field($name, '', '', $parameters) . "\n";
    }

    $fieldset .= $max;

    return $fieldset;
}

function drawSlider($name, $default = 0, $min = '', $max = '', $view = 0)
{

    $fieldset = "<div style=\"width:390px;\"><div id=\"track" . $name . "\" style=\"cursor:pointer;width:390px;background-color:#aaa;height:5px;\"><div id=\"handle" . $name . "\" class=\"slider\"> </div></div>";

    if ($view == 0) $fieldset .= draw_hidden_field($name, $default, 'id="feld_' . $name . '"');

    $fieldset .= '<p style="float:left;">' . $min . ' </p>';
    $fieldset .= '<p style="text-align:right;float:right;">' . $max . '</p>';

    $fieldset .= '</div>';
    $fieldset .= '<div style="clear:both;">&nbsp;</div>';

    if ($view != 0) {
        $fieldset .= "<script type=\"text/javascript\">
		  // <![CDATA[
				var s" . $name . " = new Control.Slider('handle" . $name . "','track" . $name . "',{sliderValue:" . $default . ",values:[1,2,3,4,5],range:\$R(1,5)})
		      	s" . $name . ".setValue(" . $default . ");
		      	s" . $name . ".setDisabled();
		  // ]]>
		  </script>";
    } else {
        $fieldset .= "<script type=\"text/javascript\">
		  // <![CDATA[
				var s" . $name . " = new Control.Slider('handle" . $name . "','track" . $name . "',{sliderValue:" . $default . ",values:[1,2,3,4,5],range:\$R(1,5),
		        onSlide:function(v){\$('feld_" . $name . "').value= v}
		        onChange:function(v){\$('feld_" . $name . "').value = v}})
		  // ]]>
		  </script>";
    }

    return $fieldset;

}