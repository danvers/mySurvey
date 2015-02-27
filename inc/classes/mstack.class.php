<?php

/**
 * Security Handler
 */
if (!defined('IN_PAGE')) {
    header('Location:' . HOME_DIR);
}

/**
 * This class is built upon the message stack handling from oscommerce 2.0
 *
 * Example usage:
 * $messageStack = new messageStack();
 * $messageStack->add('general', 'Error: Error 1', 'error');
 * $messageStack->add('general', 'Error: Error 2', 'warning');
 * if ($messageStack->size('general') > 0) echo $messageStack->output('general');
 */
class messageStack
{

// class constructor
    public function __construct()
    {

        $this->messages = array();

        if (isset($_SESSION['messageToStack'])) {
            for ($i = 0, $n = sizeof($_SESSION['messageToStack']); $i < $n; $i++) {

                $this->add($_SESSION['messageToStack'][$i]['class'], $_SESSION['messageToStack'][$i]['text'], $_SESSION['messageToStack'][$i]['type']);
            }
            unset($_SESSION['messageToStack']);
        }
    }

// class methods
    function add($class, $message, $type = 'error')
    {
        if ($type == 'error') {
            $this->messages[] = array('params' => 'class="StackError"', 'class' => $class, 'text' => $message);
        } elseif ($type == 'success') {
            $this->messages[] = array('params' => 'class="StackSuccess"', 'class' => $class, 'text' => $message);
        } else {
            $this->messages[] = array('params' => 'class="StackError"', 'class' => $class, 'text' => $message);
        }
    }

    public function add_session($class, $message, $type = 'error')
    {

        if (!isset($_SESSION['messageToStack'])) {
            $_SESSION['messageToStack'] = array();
        }

        $_SESSION['messageToStack'][] = array('class' => $class, 'text' => $message, 'type' => $type);
    }

    function reset()
    {
        $this->messages = array();
    }

    function output($class)
    {
        $output = '<div class="mStack">';
        for ($i = 0, $n = sizeof($this->messages); $i < $n; $i++) {
            if ($this->messages[$i]['class'] == $class) {
                $output .= '<p ' . $this->messages[$i]['params'] . '>' . $this->messages[$i]['text'] . '</p>';
            }
        }
        $output .= '</div>';
        return $output;
    }

    function size($class)
    {
        $count = 0;

        for ($i = 0, $n = sizeof($this->messages); $i < $n; $i++) {
            if ($this->messages[$i]['class'] == $class) {
                $count++;
            }
        }

        return $count;
    }
}
