<?php
/*
Plugin Name: My Plugin
Plugin URI: http://abhirama.wordpress.com 
Description: A plugin built as part of WordPress plugin tutorial.
Version: 1
Author: Abhirama
Author URI: http://abhirama.wordpress.com 
*/


global $my_plugin_table;
global $my_plugin_db_version;
global $wpdb;
$my_plugin_table = $wpdb->prefix . 'my_plugin';
$my_plugin_db_version = '1.0';

register_activation_hook( __FILE__,  'my_plugin_install' );

function my_plugin_install() {
  global $wpdb;
  global $my_plugin_table;
  global $my_plugin_db_version;

  if ( $wpdb->get_var( "show tables like '$my_plugin_table'" ) != $my_plugin_table ) {
    $sql = "CREATE TABLE $my_plugin_table (". 
	     "id int NOT NULL AUTO_INCREMENT, ".
	     "user_text text NOT NULL, ".
	     "UNIQUE KEY id (id) ".
			 ")";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( "my_plugin_db_version", $my_plugin_db_version );
  }
}

class MyWidget extends WP_Widget {
  function MyWidget() {
    parent::WP_Widget( false, $name = 'My Widget' );
  }

  function widget( $args, $instance ) {
    extract( $args );
    $title = apply_filters( 'widget_title', $instance['title'] );
    ?>

    <?php
	echo $before_widget;
    ?>

    <?php
      if ($title) {
	echo $before_title . $title . $after_title;
      }
    ?>

    <div class="my_textbox">
      <input type='text' name='my_text' id='my_text_id'/>
      <input type='button' value='Submit' id='my_text_submit_id'/>
    </div>

     <?php
       echo $after_widget;
     ?>
     <?php
  }

  function update( $new_instance, $old_instance ) {
    return $new_instance;
  }

  function form( $instance ) {
    $title = esc_attr( $instance['title'] );
    ?>

    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
      <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
      </label>
    </p>
    <?php
  }
}

add_action( 'widgets_init', 'MyWidgetInit' );
function MyWidgetInit() {
  register_widget( 'MyWidget' );
}

function my_widget_action() {
  global $wpdb;
  global $my_plugin_table;
  $user_text =  $wpdb->escape( $_POST['user_text'] );
  $query = "INSERT INTO $my_plugin_table (user_text) VALUES ('$user_text')";
  $wpdb->query( $query );
  echo 'success';
}

add_action( 'wp_ajax_my_widget_action', 'my_widget_action' );
add_action( 'wp_ajax_nopriv_my_widget_action', 'my_widget_action' );

add_action( 'wp_head', 'my_plugin_js_header' );

function my_plugin_js_header() {
  ?>
   <script type="text/javascript">
     //<![CDATA[
     jQuery(document).ready(function($) {
       jQuery('#my_text_submit_id').click(function(){
       jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', { 'action': 'my_widget_action',  'user_text': jQuery('#my_text_id').val() }	);
       });
     });
     //]]>
   </script>
  <?php
}

?>
