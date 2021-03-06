<div class="article-masonry-meta-wrapper">
	<?php if(has_category()) :  ?>
	<div class="category-meta">
		<em><?php _e("Filled under :", 'jeg_textdomain'); ?> </em>
		<?php
			$catlink = array();

			foreach(get_the_category() as $category) :
				$catlink[] = '<a href="' . get_category_link($category->term_id) . '" title="' . $category->description . '">' . $category->name . '</a>';
			endforeach;

			echo implode(' , ', $catlink);
		?>
	</div>
	<?php endif; ?>

	<?php if(has_tag()) : ?>
	<div class="tag-meta">
		<em><?php _e("Tagged on :", 'jeg_textdomain'); ?></em>
		<?php
			$taglink = array();

			foreach(get_the_tags() as $tag) :
				$taglink[] = '<a href="' . get_tag_link($tag->term_id) . '" title="' . $tag->description . '">' . $tag->name . '</a>';
			endforeach;

			echo implode(' , ', $taglink);
		?>
	</div>
	<?php endif; ?>

	<div class="comment-meta">
		<em><?php _e("Response:", 'jeg_textdomain'); ?> </em>
		<a href="<?php echo get_permalink();?>#comments">
			<?php
				$zerocomment 	= __( 'No Comment Yet', 'jeg_textdomain');
				$onecomment 	= __('1 Comment', 'jeg_textdomain');
				$morecomment 	= __('% Comments', 'jeg_textdomain');
				comments_number($zerocomment, $onecomment, $morecomment);
			?>
		</a>
	</div>

</div>