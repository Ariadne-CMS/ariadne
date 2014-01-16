<?php
/**
 * Theme function for a header line in the diff.
 */
function diff_header_line($vars) {
	return '<strong>Line ' . $vars['lineno']. '</strong>';
}

function check_plain($text) {
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Theme function for a content line in the diff.
 */
function diff_content_line($vars) {
  return '<div>'. $vars['line'] .'</div>';
}

/**
 * Theme function for an empty line in the diff.
 */
function diff_empty_line($vars) {
  return $vars['line'];
}

/**
 * Diff formatter for Ariadne
 * @private
 * @subpackage DifferenceEngine
 */
class AriadneDiffFormatter extends DiffFormatter {

  var $rows;

  function __construct() {
    $this->leading_context_lines = 2;
    $this->trailing_context_lines = 2;
  }

  function _start_diff() {
    $this->rows = array();
  }

  function _end_diff() {
    return $this->rows;
  }

  function _block_header($xbeg, $xlen, $ybeg, $ylen) {
    return array(
      array(
        'data' => diff_header_line(array('lineno' => $xbeg)),
        'colspan' => 2,
      ),
      array(
        'data' => diff_header_line(array('lineno' => $ybeg)),
        'colspan' => 2,
      )
    );
  }

  function _start_block($header) {
    if ($this->show_header) {
      $this->rows[] = $header;
    }
  }

  function _end_block() {
  }

  function _lines($lines, $prefix=' ', $color='white') {
  }

  /**
   * Note: you should HTML-escape parameter before calling this.
   */
  function addedLine($line) {
    return array(
      '+',
      array(
        'data' => diff_content_line(array('line' => $line)),
        'class' => 'diff-addedline',
      )
    );
  }

  /**
   * Note: you should HTML-escape parameter before calling this.
   */
  function deletedLine($line) {
    return array(
      '-',
      array(
        'data' => diff_content_line(array('line' => $line)),
        'class' => 'diff-deletedline',
      )
    );
  }

  /**
   * Note: you should HTML-escape parameter before calling this.
   */
  function contextLine($line) {
    return array(
      '&nbsp;',
      array(
        'data' => diff_content_line(array('line' => $line)),
        'class' => 'diff-context',
      )
    );
  }

  function emptyLine() {
    return array(
      '&nbsp;',
      diff_empty_line(array('line' => '&nbsp;')),
    );
  }

  function _added($lines) {
    foreach ($lines as $line) {
      $this->rows[] = array_merge($this->emptyLine(), $this->addedLine(check_plain($line)));
    }
  }

  function _deleted($lines) {
    foreach ($lines as $line) {
      $this->rows[] = array_merge($this->deletedLine(check_plain($line)), $this->emptyLine());
    }
  }

  function _context($lines) {
    foreach ($lines as $line) {
      $this->rows[] = array_merge($this->contextLine(check_plain($line)), $this->contextLine(check_plain($line)));
    }
  }

  function _changed($orig, $closing) {
    $diff = new WordLevelDiff($orig, $closing);
    $del = $diff->orig();
    $add = $diff->closing();

    // Notice that WordLevelDiff returns HTML-escaped output.
    // Hence, we will be calling addedLine/deletedLine without HTML-escaping.

    while ($line = array_shift($del)) {
      $aline = array_shift( $add );
      $this->rows[] = array_merge($this->deletedLine($line), $this->addedLine($aline));
    }
    foreach ($add as $line) {  // If any leftovers
      $this->rows[] = array_merge($this->emptyLine(), $this->addedLine($line));
    }
  }
}

?>