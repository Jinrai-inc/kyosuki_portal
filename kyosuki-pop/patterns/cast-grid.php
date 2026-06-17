<?php
/**
 * Title: 出演者カラーカード
 * Slug: kyosuki-pop/cast-grid
 * Categories: kyosuki-list
 */
?>
<!-- wp:group {"className":"kp-section"} -->
<div class="wp-block-group kp-section">
  <h2 class="kp-section__head">★ MEMBERS ♡ ★</h2>
  <!-- wp:query {"queryId":99,"query":{"perPage":6,"postType":"cast","inherit":false}} -->
  <div class="wp-block-query">
    <!-- wp:post-template {"layout":{"type":"grid","columnCount":6}} -->
      <!-- wp:group {"className":"kp-cast"} -->
      <div class="wp-block-group kp-cast">
        <!-- wp:post-featured-image {"isLink":true,"className":"kp-cast__avatar"} /-->
        <!-- wp:post-title {"isLink":true,"className":"kp-cast__name"} /-->
      </div>
      <!-- /wp:group -->
    <!-- /wp:post-template -->
  </div>
  <!-- /wp:query -->
</div>
<!-- /wp:group -->
