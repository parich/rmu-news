<?php
// file: src/rmu-news/render.php
?>
<div id="rmu-news-container" class="rmu-news-shortcode"
	data-category="<?php echo esc_attr($shortcode_atts['category'] ?? 'สมัครงาน'); ?>"
	data-limit="<?php echo esc_attr($shortcode_atts['limit'] ?? 20); ?>">
	<div class="rmu-news-search-container">
		<div class="rmu-news-search-row">
			<input type="text" id="rmu-news-search" placeholder="ค้นหาข่าว" />
			<div class="rmu-news-view-toggle">
				<button type="button" class="view-toggle-btn" data-view="list" title="รูปแบบรายการ">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
						<path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
					</svg>
				</button>
				<button type="button" class="view-toggle-btn" data-view="card" title="รูปแบบการ์ด">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
						<path d="M10 4H4c-1.11 0-2 .89-2 2v3c0 1.11.89 2 2 2h6c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm10 0h-6c-1.11 0-2 .89-2 2v3c0 1.11.89 2 2 2h6c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm-10 9H4c-1.11 0-2 .89-2 2v3c0 1.11.89 2 2 2h6c1.11 0 2-.89 2-2v-3c0-1.11-.89-2-2-2zm10 0h-6c-1.11 0-2 .89-2 2v3c0 1.11.89 2 2 2h6c1.11 0 2-.89 2-2v-3c0-1.11-.89-2-2-2z"/>
					</svg>
				</button>
			</div>
		</div>
	</div>
	<div class="rmu-news-loading" style="display: none;">กำลังโหลด...</div>
	<div class="rmu-news-content"></div>
</div>