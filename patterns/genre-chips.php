<?php
/**
 * Title: ジャンル別カラーチップ
 * Slug: kyosuki-pop/genre-chips
 * Categories: kyosuki, kyosuki-list
 * Description: 登録されている kp_arc タームをカラーチップとして並べる動的パターン
 */
?>
<!-- wp:html -->
<div class="kp-section">
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <?php
    $arcs   = get_terms( array( 'taxonomy' => 'kp_arc', 'hide_empty' => false, 'number' => 8 ) );
    $tones  = array( '#5AC8FA', '#FF7AC6', '#FFE066', '#C9F28A', '#9B5DE5', '#E83E8C' );
    $emojis = array( '🏝', '❄️', '🎒', '🌸', '☀️', '🎓' );
    if ( ! is_wp_error( $arcs ) && $arcs ) {
      foreach ( $arcs as $i => $t ) {
        printf(
          '<a class="kp-chip" style="background:%s;color:%s" href="%s">%s #%s</a>',
          esc_attr( $tones[ $i % count( $tones ) ] ),
          '#1A0B3D',
          esc_url( get_term_link( $t ) ),
          esc_html( $emojis[ $i % count( $emojis ) ] ),
          esc_html( $t->name )
        );
      }
    }
    ?>
  </div>
</div>
<!-- /wp:html -->
