<?php


/**
 * Security Handler
 */
if (!defined('IN_PAGE')) die();

class Categories
{

    private $db, $categories_subs, $categories_start, $tree;

    /**
     * @param $database
     * creates a categories tree, based on the data stored in the database.
     */
    public function __construct($database)
    {
        $this->db = $database;
        $this->categories_subs = array();
        $this->categories_start = array();
        $this->tree = array();
        $categories_subs_query = $this->db->query("SELECT COUNT(id) as count, parent from " . table_categories . " GROUP BY parent");
        while ($row = $this->db->fetchArray($categories_subs_query)) {
            $this->categories_subs[$row['parent']] = $row['count'];
        }

        $categories_query = $this->db->query("SELECT id, name, empty,  parent, sort_order FROM " . table_categories . " ORDER BY sort_order");
        while ($row = $this->db->fetchArray($categories_query)) {
            $this->categories_start['S_' . $row['id'] . '_' . $row['parent'] . '_E'] = $row;

            $this->tree[$row['id']] = array('name' => $row['name'],
                'parent' => $row['parent'],
                'sort_order' => $row['sort_order'],
                'empty' => $row['empty'],
                'next_id' => false);

            if (isset($parent_id)) {
                $this->tree[$parent_id]['next_id'] = $row['id'];
            }

            $parent_id = $row['id'];

            if (!isset($first_element)) {
                $first_element = $row['id'];
            }
        }
    }

    /**
     * @param $category
     * @return int number of children
     */
    public function getChildren($category)
    {
        $this->db->query('SELECT COUNT(cat_id) AS fieldCount FROM ' . table_fields . ' WHERE cat_id ="' . $category . '"');
        $result = $this->db->fetchArray();

        return (int)$result['fieldCount'];
    }

    /**
     * @param $parent   id of the parent category
     * @param $depth    depth of category
     * @return string nested category elements
     */
    public function getChildCats($parent, $depth)
    {
        $returnval = '';

        $cat_subs = array();

        foreach ($this->categories_start as $key => $value) {

            if (strstr($key, '_' . (int)$parent . '_E')) {
                $cat_subs[$parent][] = substr($key, 2, strpos($key, "_", 2) - 2);
            }
        }
        if (sizeof($cat_subs) < 1) {
            return $returnval;
        }
        foreach ($cat_subs[$parent] as $v) {

            $categories = $this->categories_start['S_' . $v . '_' . (int)$parent . '_E'];

            $returnval .= ',' . $categories['id'];

            if (array_key_exists($categories['id'], $this->categories_subs) || (array_key_exists($categories['id'], $this->categories_subs))) {

                $returnval .= $this->getChildCats($categories['id'], $depth + 1);
            }

        }
        return $returnval;
    }

    /**
     * @param $parent   id of the parent element
     * @param $default  default element
     * @param $depth    depth of the tree
     * @return string   options field of a given category
     */
    public function drawCatOptions($parent, $default, $depth)
    {
        $returnval = '';

        $cat_subs = array();
        foreach ($this->categories_start as $key => $value) {
            if (strstr($key, '_' . (int)$parent . '_E')) {
                $cat_subs[$parent][] = substr($key, 2, strpos($key, "_", 2) - 2);
            }
        }
        foreach ($cat_subs[$parent] as $v) {

            $categories = $this->categories_start['S_' . $v . '_' . (int)$parent . '_E'];
            $sign = '';

            if ($parent > 0) {
                for ($i = 0; $i < $depth; $i++) {
                    $sign .= '-';
                }
            }
            $returnval .= '<option value="' . $categories['id'] . '"';
            if ($categories['id'] == $default) {
                $returnval .= ' selected="selected"';
            }
            $returnval .= '>' . $sign . ' ' . $categories['name'] . '</option>' . "\n";

            if (array_key_exists($categories['id'], $this->categories_subs) || (array_key_exists($categories['id'], $this->categories_subs))) {

                $returnval .= $this->drawCatOptions($categories['id'], $default, $depth + 1);
            }

        }
        return $returnval;
    }

    /**
     * @param $category
     * @return mixed category
     */
    public function __get($category)
    {
        return $this->tree[$category];
    }

    public function listCategories($parent, $start_path, $linkPath, $display_field_num = false, $survey = 0)
    {
        $returnval = '';
        if (($start_path == '') && ($parent > 0)) {
            $start_path = $parent;
            if ($parent > 0) $start_path .= "_" . $parent;
        }

        if ($parent != 0) {
            $returnval .= "<ul class=\"subMenuBox\">\n";
        } else {
            $returnval .= "<ul class=\"box\">\n";
        }

        $cat_subs = array();
        foreach ($this->categories_start as $key => $value) {
            if (strstr($key, '_' . $parent . '_E')) {
                $cat_subs[$parent][] = substr($key, 2, strpos($key, "_", 2) - 2);
            }
        }
        foreach ($cat_subs[$parent] as $v) {

            $categories = $this->categories_start['S_' . $v . '_' . $parent . '_E'];

            $grouppath = $categories['id'];
            if ($survey > 0) {
                $cPath_new = "?position=" . $linkPath['position'] . "&amp;cID=" . $grouppath . "&amp;aID=" . $survey;
            } else {
                $cPath_new = "?position=" . $linkPath['position'] . "&amp;cID=" . $grouppath;
            }
            $categories_string = $linkPath['filename'] . $cPath_new;

            $completed = "";
            $isEmpty = false;
            if ($this->isEmpty($v)) $isEmpty = true;

            if (!$isEmpty && $linkPath['position'] == 'evaluate' && $this->isCompleted($survey, $v)) $completed = '<img alt="" src="img/complete.gif"/>';

            if (isset($_GET['cID']) && $v == $_GET['cID']) {
                $returnval .= "<li><strong>" . $categories['name'] . "</strong>" . ' ' . $completed;
            } elseif ($isEmpty && $survey > 0) {
                $returnval .= "<li>" . $categories['name'];
            } else {
                $returnval .= "<li><a href=\"" . $categories_string . "\">" . $categories['name'] . "</a>" . ' ' . $completed;


            }

            if ($display_field_num && $this->getFieldCount($categories['id']) > 0) {
                $returnval .= ' (' . $this->getFieldCount($categories['id']) . ')';
            }
            if (array_key_exists($categories['id'], $this->categories_subs)) {

                $returnval .= $this->listCategories($categories['id'], $start_path, $linkPath, $display_field_num, $survey);
            }
            $returnval .= "</li>\n";
        }
        $returnval .= "</ul>";
        return $returnval;
    }

    /**
     * @param $category
     * @return bool
     */
    public function isEmpty($category)
    {
        return ($this->tree[$category]['empty'] == 1);
    }

    /**
     * @param int $survey
     * @param int $category
     * @return bool
     */
    private function isCompleted($survey = 0, $category = 0)
    {
        if ($category == 0 || $survey == 0)
            return false;


        $fields = "";
        $field_array = array();

        $this->db->query('SELECT id FROM ' . table_fields . ' WHERE cat_id="' . $category . '"');


        while ($row = $this->db->fetchArray()) {
            $fields .= ', field_' . $row['id'];
            $field_array[] = $row['id'];
        }
        $fields_query = substr($fields, 1, strlen($fields));

        $this->db->query('SELECT ' . $fields_query . ' FROM ' . table_survey . ' WHERE id="' . $survey . '"');

        while ($row = $this->db->fetchArray()) {
            foreach ($field_array as $field) {
                if ($row['field_' . $field] == NULL)
                    return false;
            }
        }

        return true;
    }

    /**
     * @param $category
     * @return int
     */
    public function getFieldCount($category)
    {
        $this->db->query('SELECT COUNT(cat_id) AS fieldCount FROM ' . table_fields . ' WHERE cat_id ="' . $category . '"');
        $result = $this->db->fetchArray();

        return (int)$result['fieldCount'];
    }

    public function __destruct()
    {
        unset($this->db);
    }
}