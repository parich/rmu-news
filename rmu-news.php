<?php
/*
 * Plugin Name:       RMU News
 * Description:       Display RMU university news with search, category filtering, and customizable list/card view modes. Features responsive design and admin color customization.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rmu-news
 *
 * @package CreateBlock
 */

if (!defined('ABSPATH')) {
	exit;
}

function create_block_rmu_news_block_init()
{
	if (function_exists('wp_register_block_types_from_metadata_collection')) {
		wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
		return;
	}

	if (function_exists('wp_register_block_metadata_collection')) {
		wp_register_block_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
	}

	// ตรวจสอบว่าไฟล์ manifest มีอยู่หรือไม่
	$manifest_file = __DIR__ . '/build/blocks-manifest.php';
	if (file_exists($manifest_file)) {
		$manifest_data = require $manifest_file;
		foreach (array_keys($manifest_data) as $block_type) {
			register_block_type(__DIR__ . "/build/{$block_type}");
		}
	}
}
add_action('init', 'create_block_rmu_news_block_init');

// แก้ไข: เปลี่ยน hook และเพิ่ม frontend assets
function rmu_news_enqueue_assets()
{
	// สำหรับ Block Editor
	wp_enqueue_style(
		'rmu-news-editor-style',
		plugins_url('build/rmu-news/style-index.css', __FILE__),
		array(),
		'1.0'
	);

	wp_enqueue_script(
		'rmu-news-editor',
		plugins_url('build/rmu-news/index.js', __FILE__),
		array('wp-blocks', 'wp-element', 'wp-editor'),
		'1.0',
		true
	);
}
add_action('enqueue_block_editor_assets', 'rmu_news_enqueue_assets');

// เพิ่ม: Frontend assets
function rmu_news_enqueue_frontend_assets()
{
	wp_enqueue_style(
		'rmu-news-style',
		plugins_url('build/rmu-news/style-index.css', __FILE__),
		array(),
		'1.0'
	);

	wp_enqueue_script(
		'rmu-news-view',
		plugins_url('build/rmu-news/view.js', __FILE__),
		array(),
		'1.0',
		true
	);

	// ส่งข้อมูลการตั้งค่าไปยัง JavaScript
	wp_localize_script('rmu-news-view', 'rmuNewsSettings', array(
		'apiUrl' => get_option('rmu_news_api_url', 'https://www.rmu.ac.th/api/posts/filter'),
		'buttonColor' => get_option('rmu_news_button_color', '#2874fc'),
		'buttonTextColor' => get_option('rmu_news_button_text_color', '#e0ecff'),
		'borderColor' => get_option('rmu_news_button_border_color', '#ccc'),
		'buttonHoverColor' => get_option('rmu_news_button_hover_color', '#e0ecff'),
		'buttonHoverTextColor' => get_option('rmu_news_button_hover_text_color', '#2874fc'),
		// เพิ่มการตั้งค่าสำหรับ News Item
		'itemBackgroundColor' => get_option('rmu_news_item_background_color', '#ffffff'),
		'itemBorderColor' => get_option('rmu_news_item_border_color', '#e1e5e9'),
		'itemHoverBorderColor' => get_option('rmu_news_item_hover_border_color', '#2874fc'),
		'itemTitleColor' => get_option('rmu_news_item_title_color', '#333333'),
		'itemTitleHoverColor' => get_option('rmu_news_item_title_hover_color', '#2874fc'),
		'itemDateColor' => get_option('rmu_news_item_date_color', '#2874fc'),
		'itemMetaColor' => get_option('rmu_news_item_meta_color', '#888888'),
		'itemCategoryBackground' => get_option('rmu_news_item_category_background', '#2874fc'),
		'itemCategoryTextColor' => get_option('rmu_news_item_category_text_color', '#ffffff'),
		// เพิ่มการตั้งค่าโหมดการแสดงผล
		'defaultDisplayMode' => get_option('rmu_news_default_display_mode', 'list'),
	));

	// เพิ่ม Custom CSS สำหรับ News Item
	$custom_css = rmu_news_generate_custom_css();
	if (!empty($custom_css)) {
		wp_add_inline_style('rmu-news-style', $custom_css);
	}
}
add_action('wp_enqueue_scripts', 'rmu_news_enqueue_frontend_assets');

// ฟังก์ชันสำหรับสร้าง Custom CSS
function rmu_news_generate_custom_css()
{
	$item_bg = get_option('rmu_news_item_background_color', '#ffffff');
	$item_border = get_option('rmu_news_item_border_color', '#e1e5e9');
	$item_hover_border = get_option('rmu_news_item_hover_border_color', '#2874fc');
	$item_title = get_option('rmu_news_item_title_color', '#333333');
	$item_title_hover = get_option('rmu_news_item_title_hover_color', '#2874fc');
	$item_date = get_option('rmu_news_item_date_color', '#2874fc');
	$item_meta = get_option('rmu_news_item_meta_color', '#888888');
	$item_cat_bg = get_option('rmu_news_item_category_background', '#2874fc');
	$item_cat_text = get_option('rmu_news_item_category_text_color', '#ffffff');

	$css = "
    .rmu-news-shortcode .rmu-news-item,
    #rmu-news-container .rmu-news-item {
        background: {$item_bg} !important;
        border-color: {$item_border} !important;
    }
    
    .rmu-news-shortcode .rmu-news-item:hover,
    #rmu-news-container .rmu-news-item:hover {
        border-color: {$item_hover_border} !important;
    }
    
    .rmu-news-shortcode .rmu-news-item .rmu-news-title a,
    #rmu-news-container .rmu-news-item .rmu-news-title a {
        color: {$item_title} !important;
    }
    
    .rmu-news-shortcode .rmu-news-item .rmu-news-title a:hover,
    #rmu-news-container .rmu-news-item .rmu-news-title a:hover {
        color: {$item_title_hover} !important;
    }
    
    .rmu-news-shortcode .rmu-news-item .rmu-news-date,
    #rmu-news-container .rmu-news-item .rmu-news-date {
        color: {$item_date} !important;
        background: " . rmu_news_hex_to_rgba($item_date, 0.1) . " !important;
        border-color: " . rmu_news_hex_to_rgba($item_date, 0.2) . " !important;
    }
    
    .rmu-news-shortcode .rmu-news-item .rmu-news-meta,
    #rmu-news-container .rmu-news-item .rmu-news-meta {
        color: {$item_meta} !important;
    }
    
    .rmu-news-shortcode .rmu-news-item .rmu-news-category span,
    #rmu-news-container .rmu-news-item .rmu-news-category span {
        background: {$item_cat_bg} !important;
        color: {$item_cat_text} !important;
        box-shadow: 0 2px 4px " . rmu_news_hex_to_rgba($item_cat_bg, 0.3) . " !important;
    }
    ";

	return $css;
}

// ฟังก์ชันช่วยแปลง HEX เป็น RGBA
function rmu_news_hex_to_rgba($hex, $alpha = 1)
{
	$hex = str_replace('#', '', $hex);

	if (strlen($hex) == 3) {
		$hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
	}

	$r = hexdec(substr($hex, 0, 2));
	$g = hexdec(substr($hex, 2, 2));
	$b = hexdec(substr($hex, 4, 2));

	return "rgba($r, $g, $b, $alpha)";
}

// แก้ไข: shortcode function
function rmu_news_shortcode($atts)
{
	// กำหนดค่า default attributes
	$atts = shortcode_atts(array(
		'category' => 'สมัครงาน',
		'limit' => 10
	), $atts, 'rmu_news');

	ob_start();
	$render_file = plugin_dir_path(__FILE__) . 'build/rmu-news/render.php';

	if (file_exists($render_file)) {
		// ส่งค่า attributes ไปยังไฟล์ render
		$shortcode_atts = $atts;
		include $render_file;
	} else {
		// ถ้าไม่มีไฟล์ render.php ให้แสดงข้อความแจ้งเตือน
		?>
		<div id="rmu-news-container" class="rmu-news-shortcode">
			<p>ไม่มี render.php</p>
		</div>
		<?php
	}

	return ob_get_clean();
}
add_shortcode('rmu_news', 'rmu_news_shortcode');

function rmu_news_options_page()
{
	add_options_page(
		'RMU News Settings',
		'RMU News',
		'manage_options',
		'rmu-news',
		'rmu_news_options_page_html'
	);
}
add_action('admin_menu', 'rmu_news_options_page');

// ฟังก์ชันสำหรับรีเซ็ตค่าเริ่มต้น
function rmu_news_get_default_settings()
{
	return array(
		'api_url' => 'https://www.rmu.ac.th/api/posts/filter',
		'button_color' => '#2874fc',
		'button_text_color' => '#e0ecff',
		'button_border_color' => '#ccc',
		'button_hover_color' => '#e0ecff',
		'button_hover_text_color' => '#2874fc',
		'item_background_color' => '#ffffff',
		'item_border_color' => '#e1e5e9',
		'item_hover_border_color' => '#2874fc',
		'item_title_color' => '#333333',
		'item_title_hover_color' => '#2874fc',
		'item_date_color' => '#2874fc',
		'item_meta_color' => '#888888',
		'item_category_background' => '#2874fc',
		'item_category_text_color' => '#ffffff',
		'default_display_mode' => 'list'
	);
}

function rmu_news_options_page_html()
{
	if (!current_user_can('manage_options')) {
		return;
	}

	// Handle Reset Button
	if (isset($_POST['reset'])) {
		if (!wp_verify_nonce($_POST['rmu_news_nonce'], 'rmu_news_settings')) {
			wp_die('Security check failed');
		}

		$defaults = rmu_news_get_default_settings();
		foreach ($defaults as $key => $value) {
			update_option('rmu_news_' . $key, $value);
		}

		echo '<div class="notice notice-success"><p>ค่าเริ่มต้นถูกรีเซ็ตเรียบร้อยแล้ว!</p></div>';
	}

	// Handle Save Button
	if (isset($_POST['submit'])) {
		// ตรวจสอบ nonce
		if (!wp_verify_nonce($_POST['rmu_news_nonce'], 'rmu_news_settings')) {
			wp_die('Security check failed');
		}

		update_option('rmu_news_api_url', sanitize_text_field($_POST['api_url']));
		update_option('rmu_news_button_color', sanitize_hex_color($_POST['button_color']));
		update_option('rmu_news_button_text_color', sanitize_hex_color($_POST['button_text_color']));
		update_option('rmu_news_button_border_color', sanitize_hex_color($_POST['button_border_color']));
		update_option('rmu_news_button_hover_color', sanitize_hex_color($_POST['button_hover_color']));
		update_option('rmu_news_button_hover_text_color', sanitize_hex_color($_POST['button_hover_text_color']));

		// เพิ่มการบันทึกค่าใหม่สำหรับ News Item
		update_option('rmu_news_item_background_color', sanitize_hex_color($_POST['item_background_color']));
		update_option('rmu_news_item_border_color', sanitize_hex_color($_POST['item_border_color']));
		update_option('rmu_news_item_hover_border_color', sanitize_hex_color($_POST['item_hover_border_color']));
		update_option('rmu_news_item_title_color', sanitize_hex_color($_POST['item_title_color']));
		update_option('rmu_news_item_title_hover_color', sanitize_hex_color($_POST['item_title_hover_color']));
		update_option('rmu_news_item_date_color', sanitize_hex_color($_POST['item_date_color']));
		update_option('rmu_news_item_meta_color', sanitize_hex_color($_POST['item_meta_color']));
		update_option('rmu_news_item_category_background', sanitize_hex_color($_POST['item_category_background']));
		update_option('rmu_news_item_category_text_color', sanitize_hex_color($_POST['item_category_text_color']));

		// เพิ่มการบันทึกการตั้งค่าโหมดการแสดงผล
		update_option('rmu_news_default_display_mode', sanitize_text_field($_POST['default_display_mode']));

		// แสดงข้อความยืนยัน
		echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
	}

	// Use default values if options are not set
	$defaults = rmu_news_get_default_settings();

	$api_url = get_option('rmu_news_api_url', $defaults['api_url']);
	$button_color = get_option('rmu_news_button_color', $defaults['button_color']);
	$button_text_color = get_option('rmu_news_button_text_color', $defaults['button_text_color']);
	$button_border_color = get_option('rmu_news_button_border_color', $defaults['button_border_color']);
	$button_hover_color = get_option('rmu_news_button_hover_color', $defaults['button_hover_color']);
	$button_hover_text_color = get_option('rmu_news_button_hover_text_color', $defaults['button_hover_text_color']);

	// ค่าใหม่สำหรับ News Item
	$item_background_color = get_option('rmu_news_item_background_color', $defaults['item_background_color']);
	$item_border_color = get_option('rmu_news_item_border_color', $defaults['item_border_color']);
	$item_hover_border_color = get_option('rmu_news_item_hover_border_color', $defaults['item_hover_border_color']);
	$item_title_color = get_option('rmu_news_item_title_color', $defaults['item_title_color']);
	$item_title_hover_color = get_option('rmu_news_item_title_hover_color', $defaults['item_title_hover_color']);
	$item_date_color = get_option('rmu_news_item_date_color', $defaults['item_date_color']);
	$item_meta_color = get_option('rmu_news_item_meta_color', $defaults['item_meta_color']);
	$item_category_background = get_option('rmu_news_item_category_background', $defaults['item_category_background']);
	$item_category_text_color = get_option('rmu_news_item_category_text_color', $defaults['item_category_text_color']);

	// การตั้งค่าโหมดการแสดงผล
	$default_display_mode = get_option('rmu_news_default_display_mode', $defaults['default_display_mode']);

	?>
	<div class="wrap">
		<h1>RMU News Settings</h1>
		<form method="POST" action="">
			<?php wp_nonce_field('rmu_news_settings', 'rmu_news_nonce'); ?>

			<!-- API Settings Section -->
			<h2>การตั้งค่า API</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">API URL</th>
					<td>
						<input type="url" name="api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text" />
						<p class="description">URL สำหรับดึงข้อมูลข่าว (เช่น https://www.rmu.ac.th/api/posts/filter)</p>
					</td>
				</tr>
			</table>

			<!-- Display Mode Settings Section -->
			<h2>การตั้งค่าการแสดงผล</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">โหมดการแสดงผลเริ่มต้น</th>
					<td>
						<select name="default_display_mode">
							<option value="list" <?php selected($default_display_mode, 'list'); ?>>รายการ (List)</option>
							<option value="card" <?php selected($default_display_mode, 'card'); ?>>การ์ด (Card)</option>
						</select>
						<p class="description">เลือกรูปแบบการแสดงข่าวเริ่มต้น</p>
					</td>
				</tr>
			</table>

			<!-- Button Settings Section -->
			<h2>การตั้งค่าสีปุ่ม</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Button Color</th>
					<td>
						<input type="text" name="button_color" value="<?php echo esc_attr($button_color); ?>"
							class="color-field" />
						<p class="description">สีพื้นหลังของปุ่ม</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Button Text Color</th>
					<td>
						<input type="text" name="button_text_color" value="<?php echo esc_attr($button_text_color); ?>"
							class="color-field" />
						<p class="description">สีตัวอักษรของปุ่ม</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Button Border Color</th>
					<td>
						<input type="text" name="button_border_color" value="<?php echo esc_attr($button_border_color); ?>"
							class="color-field" />
						<p class="description">สีขอบของปุ่ม</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Button Hover Color</th>
					<td>
						<input type="text" name="button_hover_color" value="<?php echo esc_attr($button_hover_color); ?>"
							class="color-field" />
						<p class="description">สีพื้นหลังเมื่อ hover หรือ active</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Button Hover Text Color</th>
					<td>
						<input type="text" name="button_hover_text_color"
							value="<?php echo esc_attr($button_hover_text_color); ?>" class="color-field" />
						<p class="description">สีตัวอักษรเมื่อ hover หรือ active</p>
					</td>
				</tr>
			</table>

			<!-- News Item Settings Section -->
			<h2>การตั้งค่าสีข่าว (News Item)</h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Item Background Color</th>
					<td>
						<input type="text" name="item_background_color"
							value="<?php echo esc_attr($item_background_color); ?>" class="color-field" />
						<p class="description">สีพื้นหลังของกล่องข่าว</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Item Border Color</th>
					<td>
						<input type="text" name="item_border_color" value="<?php echo esc_attr($item_border_color); ?>"
							class="color-field" />
						<p class="description">สีขอบของกล่องข่าว</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Item Hover Border Color</th>
					<td>
						<input type="text" name="item_hover_border_color"
							value="<?php echo esc_attr($item_hover_border_color); ?>" class="color-field" />
						<p class="description">สีขอบของกล่องข่าวเมื่อ hover</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Title Color</th>
					<td>
						<input type="text" name="item_title_color" value="<?php echo esc_attr($item_title_color); ?>"
							class="color-field" />
						<p class="description">สีตัวอักษรหัวข้อข่าว</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Title Hover Color</th>
					<td>
						<input type="text" name="item_title_hover_color"
							value="<?php echo esc_attr($item_title_hover_color); ?>" class="color-field" />
						<p class="description">สีตัวอักษรหัวข้อข่าวเมื่อ hover</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Date Color</th>
					<td>
						<input type="text" name="item_date_color" value="<?php echo esc_attr($item_date_color); ?>"
							class="color-field" />
						<p class="description">สีตัวอักษรวันที่</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Meta Color</th>
					<td>
						<input type="text" name="item_meta_color" value="<?php echo esc_attr($item_meta_color); ?>"
							class="color-field" />
						<p class="description">สีตัวอักษรข้อมูลเสริม (เปิดอ่าน)</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Category Background</th>
					<td>
						<input type="text" name="item_category_background"
							value="<?php echo esc_attr($item_category_background); ?>" class="color-field" />
						<p class="description">สีพื้นหลังของป้ายหมวดหมู่</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Category Text Color</th>
					<td>
						<input type="text" name="item_category_text_color"
							value="<?php echo esc_attr($item_category_text_color); ?>" class="color-field" />
						<p class="description">สีตัวอักษรของป้ายหมวดหมู่</p>
					</td>
				</tr>
			</table>

			<div style="margin: 20px 0;">
				<?php submit_button('บันทึกการตั้งค่า', 'primary', 'submit', false); ?>
				<?php submit_button('รีเซ็ตค่าเริ่มต้น', 'secondary', 'reset', false, array('style' => 'margin-left: 10px;', 'onclick' => 'return confirm("คุณแน่ใจหรือไม่ที่จะรีเซ็ตค่าทั้งหมดเป็นค่าเริ่มต้น?");')); ?>
			</div>
		</form>

		<!-- Preview Section -->
		<div class="rmu-news-preview" style="margin-top: 40px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
			<h2>ตัวอย่างการแสดงผลปุ่ม</h2>
			<div class="rmu-news-buttons" style="display: flex; gap: 0; margin: 20px 0;">
				<button type="button" class="preview-button-normal" style="
					background-color: <?php echo esc_attr($button_color); ?>; 
					color: <?php echo esc_attr($button_text_color); ?>; 
					border: 1px solid <?php echo esc_attr($button_border_color); ?>; 
					padding: 12px 20px; 
					border-radius: 8px 0 0 8px;
					cursor: default;
				">ทั้งหมด</button>
				<button type="button" class="preview-button-active" style="
					background-color: <?php echo esc_attr($button_hover_color); ?>; 
					color: <?php echo esc_attr($button_hover_text_color); ?>; 
					border: 1px solid <?php echo esc_attr($button_border_color); ?>; 
					padding: 12px 20px; 
					border-left: none;
					cursor: default;
				">ข่าวสมัครงาน (Active/Hover)</button>
				<button type="button" class="preview-button-normal" style="
					background-color: <?php echo esc_attr($button_color); ?>; 
					color: <?php echo esc_attr($button_text_color); ?>; 
					border: 1px solid <?php echo esc_attr($button_border_color); ?>; 
					padding: 12px 20px; 
					border-radius: 0 8px 8px 0;
					border-left: none;
					cursor: default;
				">ข่าวประกวดราคา</button>
			</div>

			<h2>ตัวอย่างการแสดงผลข่าว</h2>
			<div class="preview-news-item" style="
				background: <?php echo esc_attr($item_background_color); ?>;
				border: 1px solid <?php echo esc_attr($item_border_color); ?>;
				border-radius: 12px;
				padding: 20px;
				max-width: 300px;
				box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			">
				<div class="preview-date-meta" style="display: flex; justify-content: space-between; margin-bottom: 15px;">
					<div class="preview-date" style="
						color: <?php echo esc_attr($item_date_color); ?>;
						background: <?php echo rmu_news_hex_to_rgba($item_date_color, 0.1); ?>;
						border: 1px solid <?php echo rmu_news_hex_to_rgba($item_date_color, 0.2); ?>;
						padding: 6px 12px;
						border-radius: 15px;
						font-size: 12px;
						font-weight: bold;
					">21 ส.ค. 2568</div>
					<div class="preview-meta" style="
						color: <?php echo esc_attr($item_meta_color); ?>;
						background: #f8f9fa;
						padding: 4px 8px;
						border-radius: 10px;
						font-size: 10px;
					">เปิดอ่าน 123 ครั้ง</div>
				</div>
				<div class="preview-title" style="margin-bottom: 15px;">
					<a href="#" style="
						color: <?php echo esc_attr($item_title_color); ?>;
						text-decoration: none;
						font-weight: 600;
						font-size: 14px;
						line-height: 1.3;
					" onmouseover="this.style.color='<?php echo esc_attr($item_title_hover_color); ?>'"
						onmouseout="this.style.color='<?php echo esc_attr($item_title_color); ?>'">ตัวอย่างหัวข้อข่าวประชาสัมพันธ์ที่สำคัญ</a>
				</div>
				<div class="preview-category">
					<span style="
						background: <?php echo esc_attr($item_category_background); ?>;
						color: <?php echo esc_attr($item_category_text_color); ?>;
						padding: 6px 12px;
						border-radius: 15px;
						font-size: 11px;
						font-weight: 500;
						box-shadow: 0 2px 4px <?php echo rmu_news_hex_to_rgba($item_category_background, 0.3); ?>;
					">ประชาสัมพันธ์</span>
				</div>
			</div>
		</div>

		<!-- Usage Instructions -->
		<div style="margin-top: 30px; padding: 20px; background: white; border: 1px solid #ddd; border-radius: 8px;">
			<h2>วิธีการใช้งาน</h2>
			<h3>Shortcode พื้นฐาน:</h3>
			<code>[rmu_news]</code>

			<h3>Shortcode แบบกำหนดค่า:</h3>
			<code>[rmu_news category="ประชาสัมพันธ์" limit="5"]</code>

			<h3>พารามิเตอร์ที่รองรับ:</h3>
			<ul style="margin-left: 20px;">
				<li><strong>category:</strong> หมวดหมู่ข่าว (ทั้งหมด, สมัครงาน, ประกวดราคา, ประชาสัมพันธ์, บริการ,
					สวัสดิการ, สรรหา)</li>
				<li><strong>limit:</strong> จำนวนข่าวที่ต้องการแสดง (เช่น 5, 10, 20)</li>
			</ul>

			<h3>ตัวอย่างการใช้งาน:</h3>
			<ul style="margin-left: 20px;">
				<li><code>[rmu_news category="ทั้งหมด" limit="10"]</code> - แสดงข่าวทั้งหมด 10 ข่าว</li>
				<li><code>[rmu_news category="สมัครงาน" limit="5"]</code> - แสดงข่าวสมัครงาน 5 ข่าว</li>
				<li><code>[rmu_news limit="15"]</code> - แสดงข่าว 15 ข่าว (หมวดเริ่มต้น)</li>
			</ul>
		</div>
	</div>

	<style>
		.rmu-news-preview .rmu-news-buttons button:hover {
			opacity: 0.8;
		}

		.form-table th {
			width: 200px;
		}

		.color-field {
			width: 100px;
		}

		.preview-news-item:hover {
			border-color:
				<?php echo esc_attr($item_hover_border_color); ?>
				!important;
			transform: translateY(-2px);
			transition: all 0.3s ease;
		}
	</style>

	<script>
		jQuery(document).ready(function ($) {
			// Initialize color picker
			$('.color-field').wpColorPicker({
				change: function (event, ui) {
					// Update preview when color changes
					setTimeout(updatePreview, 100);
				},
				clear: function () {
					// Update preview when color is cleared
					setTimeout(updatePreview, 100);
				}
			});

			function updatePreview() {
				// Button colors
				var buttonColor = $('input[name="button_color"]').val();
				var buttonTextColor = $('input[name="button_text_color"]').val();
				var buttonBorderColor = $('input[name="button_border_color"]').val();
				var buttonHoverColor = $('input[name="button_hover_color"]').val();
				var buttonHoverTextColor = $('input[name="button_hover_text_color"]').val();

				// News item colors
				var itemBg = $('input[name="item_background_color"]').val();
				var itemBorder = $('input[name="item_border_color"]').val();
				var itemHoverBorder = $('input[name="item_hover_border_color"]').val();
				var itemTitle = $('input[name="item_title_color"]').val();
				var itemTitleHover = $('input[name="item_title_hover_color"]').val();
				var itemDate = $('input[name="item_date_color"]').val();
				var itemMeta = $('input[name="item_meta_color"]').val();
				var itemCatBg = $('input[name="item_category_background"]').val();
				var itemCatText = $('input[name="item_category_text_color"]').val();

				// Update button preview
				var previewButtons = $('.rmu-news-preview .rmu-news-buttons button');

				// Update normal buttons
				$('.preview-button-normal').css({
					'background-color': buttonColor,
					'color': buttonTextColor,
					'border-color': buttonBorderColor
				});

				// Update active/hover button
				$('.preview-button-active').css({
					'background-color': buttonHoverColor,
					'color': buttonHoverTextColor,
					'border-color': buttonBorderColor
				});

				// Update news item preview
				$('.preview-news-item').css({
					'background-color': itemBg,
					'border-color': itemBorder
				});

				$('.preview-title a').css('color', itemTitle);
				$('.preview-title a').attr('onmouseover', "this.style.color='" + itemTitleHover + "'");
				$('.preview-title a').attr('onmouseout', "this.style.color='" + itemTitle + "'");

				$('.preview-date').css({
					'color': itemDate,
					'background-color': hexToRgba(itemDate, 0.1),
					'border-color': hexToRgba(itemDate, 0.2)
				});

				$('.preview-meta').css('color', itemMeta);

				$('.preview-category span').css({
					'background-color': itemCatBg,
					'color': itemCatText,
					'box-shadow': '0 2px 4px ' + hexToRgba(itemCatBg, 0.3)
				});

				// Update hover style
				var hoverStyle = '.preview-news-item:hover { border-color: ' + itemHoverBorder + ' !important; }';
				$('#dynamic-hover-style').remove();
				$('<style id="dynamic-hover-style">' + hoverStyle + '</style>').appendTo('head');
			}

			// Helper function to convert hex to rgba
			function hexToRgba(hex, alpha) {
				if (!hex) return 'rgba(0,0,0,' + alpha + ')';

				hex = hex.replace('#', '');
				if (hex.length === 3) {
					hex = hex.split('').map(function (char) {
						return char + char;
					}).join('');
				}

				var r = parseInt(hex.substring(0, 2), 16);
				var g = parseInt(hex.substring(2, 4), 16);
				var b = parseInt(hex.substring(4, 6), 16);

				return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
			}

			// Initial preview update
			updatePreview();
		});
	</script>
	<?php
}

// เพิ่ม: ฟังก์ชันสำหรับ admin color picker
function rmu_news_admin_enqueue_scripts($hook)
{
	if ('settings_page_rmu-news' !== $hook) {
		return;
	}

	wp_enqueue_style('wp-color-picker');
	wp_enqueue_script('wp-color-picker');
	wp_enqueue_script('jquery');
}
add_action('admin_enqueue_scripts', 'rmu_news_admin_enqueue_scripts');