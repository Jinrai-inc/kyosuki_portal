<?php
/**
 * Search form
 *
 * @package Kyosuki_Pop
 */
?>
<form class="kp-searchform" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="kp-s" class="screen-reader-text">検索</label>
	<input id="kp-s" type="search" name="s" placeholder="🔍 検索…" value="<?php echo esc_attr( get_search_query() ); ?>" />
	<button type="submit">GO</button>
</form>
