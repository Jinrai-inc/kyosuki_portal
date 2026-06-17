<?php
/**
 * Comments
 *
 * @package Kyosuki_Pop
 */
if ( post_password_required() ) return; ?>

<section id="comments" class="kp-comments">
	<?php if ( have_comments() ) : ?>
		<h3 class="kp-section__head">★ COMMENTS (<?php echo (int) get_comments_number(); ?>) ★</h3>
		<ol class="kp-comments__list">
			<?php wp_list_comments( array( 'style' => 'ol', 'avatar_size' => 40, 'short_ping' => true ) ); ?>
		</ol>
		<nav class="kp-pagination kp-pagination--sm">
			<?php paginate_comments_links( array( 'prev_text' => '‹', 'next_text' => '›' ) ); ?>
		</nav>
	<?php endif; ?>

	<?php if ( comments_open() ) :
		comment_form( array(
			'title_reply'         => '★ COMMENT ♡ ★',
			'label_submit'        => 'POST ♡',
			'class_submit'        => 'kp-btn kp-btn--pink',
			'comment_notes_before'=> '<p class="kp-comments__notes">★ メアド非公開です。気軽にコメしてね ♡</p>',
		) );
	endif; ?>
</section>
