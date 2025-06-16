<?php
// file: src/rmu-news/render.php
?>
<div id="rmu-news-container" class="rmu-news-shortcode"
	data-category="<?php echo esc_attr($shortcode_atts['category'] ?? 'สมัครงาน'); ?>"
	data-limit="<?php echo esc_attr($shortcode_atts['limit'] ?? 20); ?>">
	<div class="rmu-news-search-container">
		<input type="text" id="rmu-news-search" placeholder="ค้นหาโพสต์กลุ่มงานประชาสัมพันธ์" />
	</div>
	<div class="rmu-news-loading" style="display: none;">กำลังโหลด...</div>
	<div class="rmu-news-content"></div>
</div>