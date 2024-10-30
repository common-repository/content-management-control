<?php
/**
Plugin Name:  Content management control
Description:  Allows administrator to lock post from editing by other users
Version:      1.0.0
Author:       Vladyslav Trembach
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  content-management-control
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if( !class_exists('ContentManagementControl') )
{
	class ContentManagementControl
	{
		public function __construct()
		{
			add_action('add_meta_boxes', array($this, 'RegisterMetaboxes'));
			add_filter('user_has_cap', array($this, 'RestrictContentEditing'), 10, 3);
			add_action('save_post', array($this, 'SavePostMeta'), 10, 3);
		}

		public function RestrictContentEditing( $allcaps, $cap, $args )
		{
			if ( ! isset( $allcaps['manage_options'] ) )
			{
				if ( ( in_array( 'edit_others_posts', $cap ) || in_array( 'edit_published_posts', $cap ) ) && ! empty( get_post_meta( $args[2], 'cmc_lock_page', true ) ) )
				{
					unset( $allcaps['edit_others_posts'] );
					unset( $allcaps['edit_published_posts'] );
				}
			}

			return $allcaps;
		}


		public function RegisterMetaboxes()
		{
			global $post;
			$obj = get_post_type_object( $post->post_type );
			add_meta_box( 'lock_post', __( 'Lock', 'content-management-control' ) . ' ' . $obj->labels->singular_name, array($this, 'DisplayCallback'), null, 'side' );
		}


		public function DisplayCallback()
		{
			global $post;
			$checked = get_post_meta( $post->ID, 'cmc_lock_page', true ) ? 'checked' : '';
			?>
            <input type="checkbox" name="cmc_lock_page"
                   value="1" <?php echo $checked; ?>><?php _e( 'Lock ', 'content-management-control' ); ?>
			<?php
		}

		public function SavePostMeta( $post_id, $post, $update )
		{
			if (isset($_POST['cmc_lock_page']) && intval($_POST['cmc_lock_page']) === 1)
			{
				update_post_meta($post_id, 'cmc_lock_page', 1);
			}
            else
			{
				delete_post_meta($post_id, 'cmc_lock_page', 1);
			}
		}
	}
}

function CMCInit()
{
	if (is_admin()) {
		new ContentManagementControl();
	}
}

// initialize
add_action( 'init', 'CMCInit' );


