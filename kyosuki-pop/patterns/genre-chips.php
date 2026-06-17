<?php
/**
 * Title: ジャンル別カラーチップ
 * Slug: kyosuki-pop/genre-chips
 * Categories: kyosuki, kyosuki-list
 */
?>
<!-- wp:html -->
<div class="kp-section">
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <?php
    $items = array(
      array( '🏝', '沖縄編',   '#5AC8FA' ),
      array( '❄️', '北海道編', '#FF7AC6' ),
      array( '🎒', '修学旅行', '#FFE066' ),
      array( '🎓', '卒業編',   '#C9F28A' ),
      array( '☀️', '夏休み',   '#9B5DE5' ),
      array( '🌸', '春休み',   '#FF7AC6' ),
    );
    foreach ( $items as $it ) {
      printf(
        '<a class="kp-chip" style="background:%s;color:%s" href="#">%s #%s</a>',
        esc_attr( $it[2] ),
        '#1A0B3D',
        esc_html( $it[0] ),
        esc_html( $it[1] )
      );
    }
    ?>
  </div>
</div>
<!-- /wp:html -->
