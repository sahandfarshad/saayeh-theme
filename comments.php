<?php
/**
 * Comments.
 *
 * @package Saayeh
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">
	<?php if ( have_comments() ) : ?>
		<h2 class="x-h2" style="font-size:24px;margin-bottom:16px">
			<?php
			/* translators: %s: count */
			echo esc_html( sprintf( __( '%s دیدگاه', 'saayeh' ), saayeh_num( get_comments_number() ) ) );
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 44,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="no-comments"><?php esc_html_e( 'امکان ثبت دیدگاه بسته شده است.', 'saayeh' ); ?></p>
	<?php endif; ?>
	<?php
	comment_form(
		array(
			'title_reply'        => __( 'دیدگاه خود را بنویسید', 'saayeh' ),
			'label_submit'       => __( 'ارسال دیدگاه', 'saayeh' ),
			'class_submit'       => 'x-btn',
			'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title" style="font-size:20px">',
			'title_reply_after'  => '</h2>',
		)
	);
	?>
</section>
