<?php
/**
 * Render: kyosuki/scoop-hero
 *
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$post_id = ! empty( $attributes['postId'] ) ? (int) $attributes['postId'] : 0;
if ( ! $post_id ) {
	$latest = get_posts( array( 'posts_per_page' => 1, 'post_status' => 'publish' ) );
	$post_id = $latest ? $latest[0]->ID : 0;
}

$tone_map = array(
	'pink'   => '#FF7AC6',
	'blue'   => '#5AC8FA',
	'yellow' => '#FFE066',
	'purple' => '#9B5DE5',
);
$tone = $tone_map[ $attributes['tone'] ] ?? '#FF7AC6';

$title    = $post_id ? get_the_title( $post_id ) : __( 'タイトル未設定', 'kyosuki-pop' );
$excerpt  = $post_id ? wp_trim_words( get_the_excerpt( $post_id ), 40 ) : '';
$link     = $post_id ? get_permalink( $post_id ) : '#';
$thumb    = $post_id && has_post_thumbnail( $post_id )
	? get_the_post_thumbnail_url( $post_id, 'large' )
	: kyosuki_pop_placeholder_url( 0 );

$highlight = trim( (string) $attributes['highlightWord'] );
$marked = $title;
if ( $highlight !== '' ) {
	$marked = preg_replace( '/' . preg_quote( $highlight, '/' ) . '/u', '<mark>$0</mark>', esc_html( $title ), 1 );
} else {
	$marked = esc_html( $title );
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'kp-scoop' ) );
?>
<section <?php echo $wrapper; ?>>
	<div class="kp-scoop__grid">
		<div class="kp-scoop__media" style="background:<?php echo esc_attr( $tone ); ?>">
			<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
			<span class="kp-scoop__sticker"><?php echo esc_html( $attributes['sticker'] ); ?></span>
			<span class="kp-scoop__circle"><?php echo nl2br( esc_html( $attributes['circle'] ) ); ?></span>
		</div>
		<div class="kp-scoop__body">
			<p class="kp-scoop__kicker"><?php echo esc_html( $attributes['kicker'] ); ?></p>
			<h2 class="kp-scoop__title"><?php echo $marked; // safe: title escaped above ?></h2>
			<?php if ( $excerpt ) : ?>
				<p class="kp-scoop__excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<a class="kp-scoop__cta" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $attributes['ctaLabel'] ); ?></a>
		</div>
	</div>
</section>
