<?php
/**
 * Main plugin class.
 *
 * @package JidobaMediaLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Jidoba_Media_Library {
	/** @var Jidoba_Media_Library|null */
	private static $instance = null;

	/** @var bool */
	private $assets_localized = false;

	/**
	 * Return the single plugin instance.
	 *
	 * @return Jidoba_Media_Library
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_content_types' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_jidoba_podcast', array( $this, 'save_podcast_meta' ) );
		add_action( 'save_post_jidoba_book', array( $this, 'save_book_meta' ) );
		add_action( 'admin_notices', array( $this, 'editor_shortcode_notice' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
		// Load early enough for page-builder preview iframes and AJAX-rendered shortcodes.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_assets' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_assets' ) );
		add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		add_shortcode( 'jidoba_podcasts', array( $this, 'podcasts_shortcode' ) );
		add_shortcode( 'jidoba_books', array( $this, 'books_shortcode' ) );

		add_action( 'wp_ajax_jidoba_filter_media', array( $this, 'ajax_filter_media' ) );
		add_action( 'wp_ajax_nopriv_jidoba_filter_media', array( $this, 'ajax_filter_media' ) );

		add_filter( 'manage_jidoba_podcast_posts_columns', array( $this, 'podcast_columns' ) );
		add_action( 'manage_jidoba_podcast_posts_custom_column', array( $this, 'render_podcast_column' ), 10, 2 );
		add_filter( 'manage_jidoba_book_posts_columns', array( $this, 'book_columns' ) );
		add_action( 'manage_jidoba_book_posts_custom_column', array( $this, 'render_book_column' ), 10, 2 );
	}

	public static function activate() {
		self::instance()->register_content_types();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	/** Register post types and their filter taxonomies. */
	public function register_content_types() {
		register_post_type(
			'jidoba_podcast',
			array(
				'labels' => array(
					'name' => __( 'Podcasts', 'jidoba-media-library' ),
					'singular_name' => __( 'Podcast', 'jidoba-media-library' ),
					'add_new_item' => __( 'Add New Podcast', 'jidoba-media-library' ),
					'edit_item' => __( 'Edit Podcast', 'jidoba-media-library' ),
					'new_item' => __( 'New Podcast', 'jidoba-media-library' ),
					'view_item' => __( 'View Podcast', 'jidoba-media-library' ),
					'search_items' => __( 'Search Podcasts', 'jidoba-media-library' ),
					'not_found' => __( 'No podcasts found.', 'jidoba-media-library' ),
					'menu_name' => __( 'Podcasts', 'jidoba-media-library' ),
				),
				'public' => true,
				'show_in_rest' => true,
				'has_archive' => true,
				'rewrite' => array( 'slug' => 'podcasts' ),
				'menu_icon' => 'dashicons-microphone',
				'supports' => array( 'title', 'editor', 'excerpt', 'revisions' ),
			),
		);

		register_post_type(
			'jidoba_book',
			array(
				'labels' => array(
					'name' => __( 'Books', 'jidoba-media-library' ),
					'singular_name' => __( 'Book', 'jidoba-media-library' ),
					'add_new_item' => __( 'Add New Book', 'jidoba-media-library' ),
					'edit_item' => __( 'Edit Book', 'jidoba-media-library' ),
					'new_item' => __( 'New Book', 'jidoba-media-library' ),
					'view_item' => __( 'View Book', 'jidoba-media-library' ),
					'search_items' => __( 'Search Books', 'jidoba-media-library' ),
					'not_found' => __( 'No books found.', 'jidoba-media-library' ),
					'menu_name' => __( 'Books', 'jidoba-media-library' ),
				),
				'public' => true,
				'show_in_rest' => true,
				'has_archive' => true,
				'rewrite' => array( 'slug' => 'books' ),
				'menu_icon' => 'dashicons-book-alt',
				'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			),
		);

		register_taxonomy(
			'jidoba_podcast_topic',
			'jidoba_podcast',
			array(
				'labels' => array(
					'name' => __( 'Podcast Topics', 'jidoba-media-library' ),
					'singular_name' => __( 'Podcast Topic', 'jidoba-media-library' ),
				),
				'public' => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite' => array( 'slug' => 'podcast-topic' ),
			)
		);

		register_taxonomy(
			'jidoba_book_genre',
			'jidoba_book',
			array(
				'labels' => array(
					'name' => __( 'Book Genres', 'jidoba-media-library' ),
					'singular_name' => __( 'Book Genre', 'jidoba-media-library' ),
				),
				'public' => true,
				'hierarchical' => true,
				'show_in_rest' => true,
				'rewrite' => array( 'slug' => 'book-genre' ),
			)
		);
	}

	/** Add editor fields and shortcode help. */
	public function add_meta_boxes() {
		add_meta_box( 'jidoba_podcast_details', __( 'Podcast Details', 'jidoba-media-library' ), array( $this, 'podcast_meta_box' ), 'jidoba_podcast', 'normal', 'high' );
		add_meta_box( 'jidoba_book_details', __( 'Book Details', 'jidoba-media-library' ), array( $this, 'book_meta_box' ), 'jidoba_book', 'normal', 'high' );
		add_meta_box( 'jidoba_podcast_shortcode', __( 'Display Shortcode', 'jidoba-media-library' ), array( $this, 'podcast_shortcode_box' ), 'jidoba_podcast', 'side', 'default' );
		add_meta_box( 'jidoba_book_shortcode', __( 'Display Shortcode', 'jidoba-media-library' ), array( $this, 'book_shortcode_box' ), 'jidoba_book', 'side', 'default' );
	}

	public function podcast_meta_box( $post ) {
		wp_nonce_field( 'jidoba_save_podcast', 'jidoba_podcast_nonce' );
		$url = get_post_meta( $post->ID, '_jidoba_podcast_url', true );
		?>
		<p>
			<label for="jidoba_podcast_url"><strong><?php esc_html_e( 'Listen link', 'jidoba-media-library' ); ?></strong></label>
			<input class="widefat" type="url" id="jidoba_podcast_url" name="jidoba_podcast_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://example.com/listen">
		</p>
		<p class="description"><?php esc_html_e( 'Use the title above for the episode title and the main editor for its description.', 'jidoba-media-library' ); ?></p>
		<?php
	}

	public function book_meta_box( $post ) {
		wp_nonce_field( 'jidoba_save_book', 'jidoba_book_nonce' );
		$author = get_post_meta( $post->ID, '_jidoba_book_author', true );
		$url = get_post_meta( $post->ID, '_jidoba_purchase_url', true );
		?>
		<p>
			<label for="jidoba_book_author"><strong><?php esc_html_e( 'Author', 'jidoba-media-library' ); ?></strong></label>
			<input class="widefat" type="text" id="jidoba_book_author" name="jidoba_book_author" value="<?php echo esc_attr( $author ); ?>">
		</p>
		<p>
			<label for="jidoba_purchase_url"><strong><?php esc_html_e( 'Purchase link', 'jidoba-media-library' ); ?></strong></label>
			<input class="widefat" type="text" inputmode="url" id="jidoba_purchase_url" name="jidoba_purchase_url" value="<?php echo esc_attr( $url ); ?>" placeholder="https://example.com/buy or #">
		</p>
		<p class="description"><?php esc_html_e( 'Use Featured Image for the book cover and the title field above for the book title.', 'jidoba-media-library' ); ?></p>
		<?php
	}

	public function podcast_shortcode_box() {
		echo '<p>' . esc_html__( 'Paste this shortcode into any page or post:', 'jidoba-media-library' ) . '</p><code>[jidoba_podcasts]</code>';
		echo '<p class="description">' . esc_html__( 'Options: per_page, topic, show_filters.', 'jidoba-media-library' ) . '</p>';
	}

	public function book_shortcode_box() {
		echo '<p>' . esc_html__( 'Paste this shortcode into any page or post:', 'jidoba-media-library' ) . '</p><code>[jidoba_books]</code>';
		echo '<p class="description">' . esc_html__( 'Options: per_page, genre, show_filters, columns.', 'jidoba-media-library' ) . '</p>';
	}

	private function can_save( $post_id, $nonce_name, $nonce_action ) {
		if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_name ] ) ), $nonce_action ) ) {
			return false;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		return current_user_can( 'edit_post', $post_id );
	}

	public function save_podcast_meta( $post_id ) {
		if ( ! $this->can_save( $post_id, 'jidoba_podcast_nonce', 'jidoba_save_podcast' ) ) {
			return;
		}
		$url = isset( $_POST['jidoba_podcast_url'] ) ? esc_url_raw( wp_unslash( $_POST['jidoba_podcast_url'] ) ) : '';
		update_post_meta( $post_id, '_jidoba_podcast_url', $url );
	}

	public function save_book_meta( $post_id ) {
		if ( ! $this->can_save( $post_id, 'jidoba_book_nonce', 'jidoba_save_book' ) ) {
			return;
		}
		$author = isset( $_POST['jidoba_book_author'] ) ? sanitize_text_field( wp_unslash( $_POST['jidoba_book_author'] ) ) : '';
		$url = isset( $_POST['jidoba_purchase_url'] ) ? esc_url_raw( wp_unslash( $_POST['jidoba_purchase_url'] ) ) : '';
		update_post_meta( $post_id, '_jidoba_book_author', $author );
		update_post_meta( $post_id, '_jidoba_purchase_url', $url );
	}

	/** Show the relevant shortcode on post-type list screens too. */
	public function editor_shortcode_notice() {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, array( 'jidoba_podcast', 'jidoba_book' ), true ) || 'edit' !== $screen->base ) {
			return;
		}
		$shortcode = 'jidoba_podcast' === $screen->post_type ? '[jidoba_podcasts]' : '[jidoba_books]';
		printf(
			'<div class="notice notice-info"><p><strong>%1$s</strong> <code>%2$s</code></p></div>',
			esc_html__( 'Display this library with:', 'jidoba-media-library' ),
			esc_html( $shortcode )
		);
	}

	public function register_assets() {
		wp_register_style( 'jidoba-media-library', JIDOBA_MEDIA_URL . 'assets/css/library.css', array(), JIDOBA_MEDIA_VERSION );
		wp_register_script( 'jidoba-media-library', JIDOBA_MEDIA_URL . 'assets/js/library.js', array(), JIDOBA_MEDIA_VERSION, true );
	}

	public function enqueue_assets() {
		if ( ! wp_style_is( 'jidoba-media-library', 'registered' ) ) {
			$this->register_assets();
		}
		wp_enqueue_style( 'jidoba-media-library' );
		wp_enqueue_script( 'jidoba-media-library' );
		if ( ! $this->assets_localized ) {
			wp_localize_script(
				'jidoba-media-library',
				'jidobaMediaLibrary',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'jidoba_filter_media' ),
					'errorText' => __( 'Could not load the library. Please try again.', 'jidoba-media-library' ),
				)
			);
			$this->assets_localized = true;
		}
	}

	public function podcasts_shortcode( $atts ) {
		$atts = shortcode_atts(
			array( 'per_page' => 4, 'topic' => '', 'show_filters' => 'yes' ),
			$atts,
			'jidoba_podcasts'
		);
		return $this->library_shortcode( 'podcast', $atts );
	}

	public function books_shortcode( $atts ) {
		$atts = shortcode_atts(
			array( 'per_page' => 8, 'genre' => '', 'show_filters' => 'yes', 'columns' => 4 ),
			$atts,
			'jidoba_books'
		);
		return $this->library_shortcode( 'book', $atts );
	}

	private function library_shortcode( $type, $atts ) {
		$this->enqueue_assets();
		$per_page = min( 50, max( 1, absint( $atts['per_page'] ) ) );
		$columns = 'book' === $type ? min( 6, max( 1, absint( $atts['columns'] ) ) ) : 1;
		$fixed_term = 'podcast' === $type ? sanitize_title( $atts['topic'] ) : sanitize_title( $atts['genre'] );
		$show_filters = in_array( strtolower( (string) $atts['show_filters'] ), array( 'yes', 'true', '1' ), true );
		$taxonomy = 'podcast' === $type ? 'jidoba_podcast_topic' : 'jidoba_book_genre';
		$id = wp_unique_id( 'jidoba-' . $type . '-' );
		$query = $this->get_query( $type, 1, $per_page, '', $fixed_term );
		$terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		ob_start();
		?>
		<section id="<?php echo esc_attr( $id ); ?>" class="jidoba-library jidoba-library--<?php echo esc_attr( $type ); ?>" data-type="<?php echo esc_attr( $type ); ?>" data-per-page="<?php echo esc_attr( $per_page ); ?>" data-fixed-term="<?php echo esc_attr( $fixed_term ); ?>" style="--jidoba-columns: <?php echo esc_attr( $columns ); ?>;">
			<?php if ( $show_filters ) : ?>
				<form class="jidoba-filters" role="search">
					<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-search"><?php esc_html_e( 'Search library', 'jidoba-media-library' ); ?></label>
					<input id="<?php echo esc_attr( $id ); ?>-search" class="jidoba-search" type="search" name="search" placeholder="<?php echo esc_attr( 'podcast' === $type ? __( 'Search podcasts…', 'jidoba-media-library' ) : __( 'Search books or authors…', 'jidoba-media-library' ) ); ?>">
					<?php if ( ! $fixed_term ) : ?>
						<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-term"><?php esc_html_e( 'Filter by category', 'jidoba-media-library' ); ?></label>
						<select id="<?php echo esc_attr( $id ); ?>-term" class="jidoba-term" name="term">
							<option value=""><?php echo esc_html( 'podcast' === $type ? __( 'All topics', 'jidoba-media-library' ) : __( 'All genres', 'jidoba-media-library' ) ); ?></option>
							<?php foreach ( $terms as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php endif; ?>
					<button class="jidoba-filter-button" type="submit"><?php esc_html_e( 'Filter', 'jidoba-media-library' ); ?></button>
				</form>
			<?php endif; ?>
			<div class="jidoba-status" aria-live="polite"></div>
			<div class="jidoba-results">
				<?php echo $this->render_results( $query, $type, 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</section>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	private function get_query( $type, $page, $per_page, $search, $term ) {
		$post_type = 'podcast' === $type ? 'jidoba_podcast' : 'jidoba_book';
		$taxonomy = 'podcast' === $type ? 'jidoba_podcast_topic' : 'jidoba_book_genre';
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'publish',
			'posts_per_page' => $per_page,
			'paged' => $page,
			's' => $search,
			'ignore_sticky_posts' => true,
		);
		if ( $term ) {
			$args['tax_query'] = array(
				array( 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $term ),
			);
		}
		return new WP_Query( $args );
	}

	private function render_results( $query, $type, $current_page ) {
		ob_start();
		if ( $query->have_posts() ) {
			echo '<div class="jidoba-items">';
			while ( $query->have_posts() ) {
				$query->the_post();
				if ( 'podcast' === $type ) {
					$this->render_podcast_card( get_the_ID() );
				} else {
					$this->render_book_card( get_the_ID() );
				}
			}
			echo '</div>';
			$this->render_pagination( $current_page, (int) $query->max_num_pages );
		} else {
			echo '<p class="jidoba-empty">' . esc_html__( 'No items matched your filter.', 'jidoba-media-library' ) . '</p>';
		}
		wp_reset_postdata();
		return ob_get_clean();
	}

	private function render_podcast_card( $post_id ) {
		$url = get_post_meta( $post_id, '_jidoba_podcast_url', true );
		$description = get_the_excerpt( $post_id );
		if ( ! $description ) {
			$description = wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 42 );
		}
		?>
		<article class="jidoba-podcast-card">
			<h3 class="jidoba-podcast-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
			<?php if ( $description ) : ?><div class="jidoba-podcast-description"><?php echo esc_html( $description ); ?></div><?php endif; ?>
			<?php if ( $url ) : ?>
				<a class="jidoba-action jidoba-listen" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">▶</span> <?php esc_html_e( 'Listen', 'jidoba-media-library' ); ?></a>
			<?php endif; ?>
		</article>
		<?php
	}

	private function render_book_card( $post_id ) {
		$author = get_post_meta( $post_id, '_jidoba_book_author', true );
		$url = get_post_meta( $post_id, '_jidoba_purchase_url', true );
		$is_placeholder_url = '#' === trim( $url );
		?>
		<article class="jidoba-book-card">
			<div class="jidoba-book-cover">
				<?php if ( has_post_thumbnail( $post_id ) ) : ?>
					<?php echo get_the_post_thumbnail( $post_id, 'large', array( 'loading' => 'lazy' ) ); ?>
				<?php else : ?>
					<div class="jidoba-cover-placeholder" aria-hidden="true"><span>📖</span></div>
				<?php endif; ?>
			</div>
			<div class="jidoba-book-body">
				<h3 class="jidoba-book-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $author ) : ?><p class="jidoba-book-author"><?php echo esc_html( sprintf( __( 'By %s', 'jidoba-media-library' ), $author ) ); ?></p><?php endif; ?>
				<?php if ( $is_placeholder_url ) : ?>
					<span class="jidoba-action jidoba-purchase" aria-disabled="true"><span aria-hidden="true">🛒</span> <?php esc_html_e( 'Purchase', 'jidoba-media-library' ); ?></span>
				<?php elseif ( $url ) : ?>
					<a class="jidoba-action jidoba-purchase" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">🛒</span> <?php esc_html_e( 'Purchase', 'jidoba-media-library' ); ?></a>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}

	private function render_pagination( $current, $total ) {
		if ( $total < 2 ) {
			return;
		}
		echo '<nav class="jidoba-pagination" aria-label="' . esc_attr__( 'Library pagination', 'jidoba-media-library' ) . '">';
		if ( $current > 1 ) {
			printf( '<button type="button" data-page="%1$d" aria-label="%2$s">‹</button>', (int) $current - 1, esc_attr__( 'Previous page', 'jidoba-media-library' ) );
		}
		$start = max( 1, $current - 2 );
		$end = min( $total, $current + 2 );
		for ( $page = $start; $page <= $end; $page++ ) {
			printf(
				'<button type="button" data-page="%1$d"%2$s%3$s>%1$d</button>',
				(int) $page,
				$page === $current ? ' class="is-current"' : '',
				$page === $current ? ' aria-current="page"' : ''
			);
		}
		if ( $current < $total ) {
			printf( '<button type="button" data-page="%1$d" aria-label="%2$s">›</button>', (int) $current + 1, esc_attr__( 'Next page', 'jidoba-media-library' ) );
		}
		echo '</nav>';
	}

	public function ajax_filter_media() {
		check_ajax_referer( 'jidoba_filter_media', 'nonce' );
		$type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		if ( ! in_array( $type, array( 'podcast', 'book' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid library type.', 'jidoba-media-library' ) ), 400 );
		}
		$page = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? min( 50, max( 1, absint( $_POST['per_page'] ) ) ) : 8;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$term = isset( $_POST['term'] ) ? sanitize_title( wp_unslash( $_POST['term'] ) ) : '';
		$query = $this->get_query( $type, $page, $per_page, $search, $term );
		wp_send_json_success(
			array(
				'html' => $this->render_results( $query, $type, $page ),
				'found' => (int) $query->found_posts,
			)
		);
	}

	public function podcast_columns( $columns ) {
		$columns['jidoba_listen_link'] = __( 'Listen Link', 'jidoba-media-library' );
		$columns['jidoba_shortcode'] = __( 'Shortcode', 'jidoba-media-library' );
		return $columns;
	}

	public function render_podcast_column( $column, $post_id ) {
		if ( 'jidoba_listen_link' === $column ) {
			$url = get_post_meta( $post_id, '_jidoba_podcast_url', true );
			echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Open link', 'jidoba-media-library' ) . '</a>' : '—';
		} elseif ( 'jidoba_shortcode' === $column ) {
			echo '<code>[jidoba_podcasts]</code>';
		}
	}

	public function book_columns( $columns ) {
		$columns['jidoba_author'] = __( 'Author', 'jidoba-media-library' );
		$columns['jidoba_shortcode'] = __( 'Shortcode', 'jidoba-media-library' );
		return $columns;
	}

	public function render_book_column( $column, $post_id ) {
		if ( 'jidoba_author' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_jidoba_book_author', true ) ?: '—' );
		} elseif ( 'jidoba_shortcode' === $column ) {
			echo '<code>[jidoba_books]</code>';
		}
	}
}
