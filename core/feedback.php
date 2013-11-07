<?php
/*
 * Module:       MyAracadePlugin user feedback module
 * Author:       Daniel Bakovic
 * Author URI:   http://myarcadeplugin.com
 */

class MyAracade_Feedback {

  // Stores the list of errors
  var $errors = array();

  // Stores the list of messages
  var $messages = array();


  function __construct($type = false, $message = '') {
    switch ($type) {
      case 'message': {
        $this->messages[] = $message;
      } break;

      case 'error': {
        $this->errors[] = $message;
      } break;

      default: {
        return;
      }
    }
  }

  /**
  * Returns error array, error string or outputs all error messages
  *
  * @param undefined $args
  *
  */
  public function get_errors( $args = array() ) {

    $defaults = array(
      'wrap_begin' => '<p class="mabp_error">',
      'wrap_end'   => '</p>',
      'output'     => 'return'
    );

    $r = wp_parse_args( $args, $defaults );
    extract($r);

    if ( !is_bool($output) && ($output == 'return') ) return $this->errors;

    $output_string = '';

    if ( $this->has_errors() ) {
      foreach ( $this->errors as $message ) {
        $output_string .= $wrap_begin.$message.$wrap_end;
      }

      if ( ( is_bool($output) && ($output === true) ) || ($output == 'echo' ) ) {
        echo $output_string;
      }
      else if ( $output == 'string') {
        return $output_string;
      }
    }
  }

  function get_messages( $args = array() ) {

    $defaults = array(
      'wrap_begin' => '<p class="mabp_info">',
      'wrap_end'   => '</p>',
      'output'     => 'return',
    );

    $r = wp_parse_args( $args, $defaults );
    extract($r);

    if ( !is_bool($output) && ($output == 'return') ) return $this->messages;

    $output_string = '';

    if ( $this->has_messages() ) {
      foreach ( $this->messages as $message ) {
        $output_string .= $wrap_begin.$message.$wrap_end;
      }

      if ( ( is_bool($output) && ($output === true) ) || ($output == 'echo') ) {
        echo $output_string;
      }
      else if ( $output == 'string') {
        return $output_string;
      }
    } else {
      return false;
    }
  }

  function add_error($message) {
    $this->errors[] = $message;
  }

  function add_message($message) {
    $this->messages[] = $message;
  }

  function has_errors() {
    if ( empty($this->errors) ) {
      return false;
    } else {
      return true;
    }
  }

  function has_messages() {
    if ( empty($this->messages) ) {
      return false;
    } else {
      return true;
    }
  }
} // end class


/**
 * Check wheather the variable is a MyArcadePlugin feedback object
 */
function is_myarcade_feedback($thing) {
  if ( is_object($thing)  && is_a($thing, 'MyAracade_Feedback') ) {
    return true;
  } else {
    return false;
  }
}

global $myarcade_feedback;
if ( !is_myarcade_feedback($myarcade_feedback) ) {
  $myarcade_feedback = new MyAracade_Feedback();
}