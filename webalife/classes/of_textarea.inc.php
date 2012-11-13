<?php
/* OOHForms: textarea
 *
 * Copyright (c) 1998 by Jay Bloodworth
 *
 * $Id: of_textarea.inc,v 1.3 2002/04/28 03:59:32 richardarcher Exp $
 */

class of_textarea extends of_element {

  var $rows;
  var $cols;
  var $wrap;

  // Constructor
  function of_textarea($a) {
    $this->setup_element($a);
  }

  function self_get($val, $which, &$count) {
    $str  = "";
    $str .= "<textarea name=\"$this->name\"";
    if($this->rows && $this->cols) $str .= " rows=\"$this->rows\" cols=\"$this->cols\"";
    if ($this->wrap) {
      $str .= " wrap=\"$this->wrap\"";
    }
    if ($this->extrahtml) {
      $str .= " $this->extrahtml";
    }
    $str .= ">" . htmlspecialchars($this->value) ."</textarea>";

    $count = 1;
    return $str;
  }

  function self_get_frozen($val, $which, &$count) {
    $str  = "";
    $str .= "<input type='hidden' name='$this->name'";
    $str .= " value=\"";
    $str .= htmlspecialchars($this->value);
    $str .= "\">\n";
    $str .= "<table border=1><tr><td>\n";
    $str .=  nl2br($this->value);
    $str .= "\n</td></tr></table>\n";

    $count = 1;
    return $str;
  }

} // end TEXTAREA

?>


