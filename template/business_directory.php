<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

get_header();
?>

<div id="Content" class="somethingfun">
	<div class="content_wrapper clearfix">

		<div class="sections_group">
			<div id="post-<?php the_ID(); ?>" <?php post_class($classes); ?>>

				<div class="section section-post-header">
					<div class="section_wrapper clearfix">


						<div class="column one post-header">
							<div class="mcb-column-inner">


								<!-- Main Profile -->
								<div class="emd-business-profile">

									<?php 
									// Instead of using the shortcode, let's make life easier.
									// echo do_shortcode('[emd_bd_postmeta tag="business_email"]'); 

									$business_meta = get_post_meta( get_the_ID(), 'business_meta', true);
									$formatted_data = maybe_unserialize($business_meta);
									// Now we can use $formatted_data['business_name']

									?>

									<div class="emd-business-logo">
										<?php the_post_thumbnail(); ?>
									</div>
									<div class="emd-business-name">
										<h2><?php echo $formatted_data['business_name']; ?>	</h2>
									</div>
									<div class="emd-business-desc">
										<p><?php echo $formatted_data['business_desc']; ?></p>
									</div>
									<div class="emd-business-contact">
										<ul>
											<li><a href="mailto:<?php echo $formatted_data['business_email']; ?>"><?php echo $formatted_data['business_email']; ?></a></li>
											<li><a href="tel:<?php echo $formatted_data['business_phone']; ?>"><?php echo $formatted_data['business_phone']; ?></a></li>
											<li><a href="https://<?php echo $formatted_data['business_website']; ?>"  target="_blank"><?php echo $formatted_data['business_website']; ?></a></li>
										</ul>
									</div>
									

								</div>


								<!-- Back to All Listings -->
								<div class="emd-business-bottom-area">
									<hr>
									<p><a href="/all-listings">Back to Listings</a></p>
								</div>

							</div>
						</div>

					</div>
				</div>

			</div>
		</div>

	</div>
</div>

<?php get_footer();
