<?php
/**
 * Title: 横スクロールカルーセル
 * Slug: kyosuki-pop/carousel
 * Categories: kyosuki-list
 */
?>
<!-- wp:group {"className":"kp-section"} -->
<div class="wp-block-group kp-section">
  <h2 class="kp-section__head">★ TOP STORIES ★</h2>
  <!-- wp:query {"queryId":50,"query":{"perPage":8,"postType":"post","inherit":false}} -->
  <div class="wp-block-query kp-carousel">
    <!-- wp:post-template -->
      <!-- wp:group {"className":"kp-card"} -->
      <div class="wp-block-group kp-card">
        <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3"} /-->
        <!-- wp:group {"className":"kp-card__body"} -->
        <div class="wp-block-group kp-card__body">
          <!-- wp:post-title {"isLink":true,"className":"kp-card__title"} /-->
        </div>
        <!-- /wp:group -->
      </div>
      <!-- /wp:group -->
    <!-- /wp:post-template -->
  </div>
  <!-- /wp:query -->
</div>
<!-- /wp:group -->
