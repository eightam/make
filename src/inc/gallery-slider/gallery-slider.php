<?php
/**
 * @package ttf-one
 */

if ( ! class_exists( 'TTF_One_Gallery_Slider' ) ) :
/**
 * class TTF_One_Gallery_Slider
 *
 * A class that adds custom logo functionality.
 *
 * @since 1.0.0
 */
class TTF_One_Gallery_Slider {

	/**
	 * The one instance of TTF_One_Gallery_Slider
	 *
	 * @since 1.0.0
	 *
	 * @var TTF_One_Gallery_Slider
	 */
	private static $instance;

	/**
	 * Instantiate or return the one TTF_One_Gallery_Slider instance.
	 *
	 * @since  1.0.0
	 *
	 * @return TTF_One_Gallery_Slider
	 */
	public static function instance() {
		if ( is_null( self::$instance ) )
			self::$instance = new self();

		return self::$instance;
	}

	/**
	 * Add the action and filter hooks.
	 *
	 * @since  1.0.0
	 *
	 * @return TTF_One_Gallery_Slider
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'post_gallery', array( $this, 'render_gallery' ), 1001, 2 );
	}

	/**
	 * Add admin-only action hooks
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function admin_init() {
		add_action( 'wp_enqueue_media', array( $this, 'enqueue_media' ), 99 );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
	}

	/**
	 * Enqueue the admin script that handles the slider settings in the Media Manager
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function enqueue_media() {
		wp_enqueue_script(
			'ttf-one-admin-gallery-settings',
			get_template_directory_uri() . '/inc/gallery-slider/gallery-slider' . TTF_ONE_SUFFIX . '.js',
			array( 'media-views' ),
			time(),
			true
		);
	}

	/**
	 * Markup for the slider settings in the Media Manager
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function print_media_templates() { ?>
		<script type="text/html" id="tmpl-ttf-one-gallery-settings">
			<h3 style="float:left;margin-top:10px;"><?php _e( 'Slider Settings', 'ttf-one' ); ?></h3>
			<label class="setting">
				<span><?php _e( 'Show gallery as slider', 'ttf-one' ); ?></span>
				<input id="ttf-one-slider" type="checkbox" data-setting="ttf_one_slider" />
			</label>
			<div id="ttf-one-slider-settings">
				<label class="setting">
					<span><?php _e( 'Auto-play', 'ttf-one' ); ?></span>
					<input type="checkbox" data-setting="ttf_one_autoplay" />
				</label>
				<label class="setting">
					<span><?php _e( 'Hide previous/next buttons', 'ttf-one' ); ?></span>
					<input type="checkbox" data-setting="ttf_one_prevnext" />
				</label>
				<label class="setting">
					<span><?php _e( 'Hide pager', 'ttf-one' ); ?></span>
					<input type="checkbox" data-setting="ttf_one_pager" />
				</label>
				<label class="setting">
					<span><?php _e( 'Delay (milliseconds)', 'ttf-one' ); ?></span>
					<input type="text" data-setting="ttf_one_delay" style="float:left;width:25%;" value="4000" />
				</label>
				<label class="setting">
					<span><?php _e( 'Effect', 'ttf-one' ); ?></span>
					<select data-setting="ttf_one_effect">
						<option value="fade" selected="selected"><?php _e( 'Cross-fade', 'ttf-one' ); ?></option>
						<option value="fadeout"><?php _e( 'Fade out', 'ttf-one' ); ?></option>
						<option value="scrollHorz"><?php _e( 'Horizontal slide', 'ttf-one' ); ?></option>
						<option value="none"><?php _e( 'None', 'ttf-one' ); ?></option>
					</select>
				</label>
			</div>
		</script>
	<?php }

	/**
	 * Alternate gallery shortcode handler for the slider
	 *
	 * @since 1.0.0
	 *
	 * @param string $output
	 * @param array $attr
	 *
     *@return string
	 */
	function render_gallery( $output, $attr ) {
		// Only use this alternative output if the slider is set to true
		if ( isset( $attr['ttf_one_slider'] ) && true == $attr['ttf_one_slider'] ) {
			$post = get_post();

			if ( ! empty( $attr['ids'] ) ) {
				// 'ids' is explicitly ordered, unless you specify otherwise.
				if ( empty( $attr['orderby'] ) )
					$attr['orderby'] = 'post__in';
				$attr['include'] = $attr['ids'];
			}

			// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
			if ( isset( $attr['orderby'] ) ) {
				$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
				if ( !$attr['orderby'] )
					unset( $attr['orderby'] );
			}

			extract( shortcode_atts( array(
				// Built-in
				'order'      => 'ASC',
				'orderby'    => 'menu_order ID',
				'id'         => $post ? $post->ID : 0,
				'size'       => 'large',
				'include'    => '',
				'exclude'    => '',
				'link'       => '',
				// ttf-one slider
				'ttf_one_slider'   => true,
				'ttf_one_autoplay' => false,
				'ttf_one_prevnext' => false,
				'ttf_one_pager'    => false,
				'ttf_one_delay'    => 4000,
				'ttf_one_effect'   => 'fade'
			), $attr, 'gallery') );

			$id = intval( $id );
			if ( 'RAND' == $order ) {
				$orderby = 'none';
			}

			if ( ! empty( $include ) ) {
				$_attachments = get_posts( array(
					'include' => $include,
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'post_mime_type' => 'image',
					'order' => $order,
					'orderby' => $orderby
				) );

				$attachments = array();
				foreach ( $_attachments as $key => $val ) {
					$attachments[$val->ID] = $_attachments[$key];
				}
			} elseif ( ! empty( $exclude ) ) {
				$attachments = get_children( array(
					'post_parent' => $id,
					'exclude' => $exclude,
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'post_mime_type' => 'image',
					'order' => $order,
					'orderby' => $orderby
				) );
			} else {
				$attachments = get_children( array(
					'post_parent' => $id,
					'post_status' => 'inherit',
					'post_type' => 'attachment',
					'post_mime_type' => 'image',
					'order' => $order,
					'orderby' => $orderby
				) );
			}

			if ( empty( $attachments ) ) {
				return '';
			}

			if ( is_feed() ) {
				$output = "\n";
				foreach ( $attachments as $att_id => $attachment )
					$output .= wp_get_attachment_link( $att_id, $size, true ) . "\n";
				return $output;
			}

			// Classes
			$classes = sanitize_html_class( 'cycle-slideshow' );

			// Data attributes
			$data_attributes = ' data-cycle-slides="figure"';

			// Autoplay
			$autoplay = (bool) $ttf_one_autoplay;
			if ( false === $autoplay ) {
				$data_attributes .= ' data-cycle-paused="true"';
			}

			// Delay
			$delay = absint( $ttf_one_delay );
			if ( 0 === $delay ) {
				$delay = 4000;
			}
			if ( 4000 !== $delay ) {
				$data_attributes .= ' data-cycle-timeout="' . esc_attr( $delay ) . '"';
			}

			// Effect
			$effect = trim( $ttf_one_effect );
			if ( ! in_array( $effect, array( 'fade', 'fadeout', 'scrollHorz', 'none' ) ) ) {
				$effect = 'fade';
			}
			if ( 'fade' !== $effect ) {
				$data_attributes .= ' data-cycle-fx="' . esc_attr( $effect ) . '"';
			}

			// Markup
			ob_start(); ?>
			<div class="<?php echo esc_attr( $classes ); ?>"<?php echo $data_attributes; ?>>
				<?php foreach ( $attachments as $id => $attachment ) : ?>
				<figure class="cycle-slide">
					<?php
					if ( ! empty( $link ) && 'file' === $link ) :
						echo wp_get_attachment_link( $id, $size, false, false );
					elseif ( ! empty( $link ) && 'none' === $link ) :
						echo wp_get_attachment_image( $id, $size, false );
					else :
						echo wp_get_attachment_link( $id, $size, true, false );
					endif;
					?>
					<?php if ( trim( $attachment->post_excerpt ) ) : ?>
					<figcaption class="cycle-caption">
						<?php echo wptexturize( $attachment->post_excerpt ); ?>
					</figcaption>
					<?php endif; ?>
				</figure>
				<?php endforeach; ?>
				<?php if ( true != $ttf_one_prevnext ) : ?>
				<div class="cycle-prev"></div>
				<div class="cycle-next"></div>
				<?php endif; ?>
				<?php if ( true != $ttf_one_pager ) : ?>
				<div class="cycle-pager"></div>
				<?php endif; ?>
			</div>
			<?php
			$output = ob_get_clean();
		}

		return $output;
	}

} // end class

if ( ! function_exists( 'ttf_one_get_logo' ) ) :
/**
 * Return the one TTF_One_Gallery_Slider object.
 *
 * @since  1.0.0
 *
 * @return TTF_One_Gallery_Slider
 */
function ttf_one_get_gallery_slider() {
	return TTF_One_Gallery_Slider::instance();
}
endif;

add_action( 'init', 'ttf_one_get_gallery_slider', 1 );

endif; // end if class_exists