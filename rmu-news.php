<?php
/**
 * Plugin Name:       Rmu News
 * Description:       Example block scaffolded with Create Block tool.
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
	));
}
add_action('wp_enqueue_scripts', 'rmu_news_enqueue_frontend_assets');

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

function rmu_news_options_page_html()
{
	if (!current_user_can('manage_options')) {
		return;
	}

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

		// แสดงข้อความยืนยัน
		echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
	}

	// Use default values if options are not set
	$api_url = get_option('rmu_news_api_url', 'https://www.rmu.ac.th/api/posts/filter');
	$button_color = get_option('rmu_news_button_color', '#2874fc');
	$button_text_color = get_option('rmu_news_button_text_color', '#e0ecff');
	$button_border_color = get_option('rmu_news_button_border_color', '#ccc');
	$button_hover_color = get_option('rmu_news_button_hover_color', '#e0ecff');
	$button_hover_text_color = get_option('rmu_news_button_hover_text_color', '#2874fc');
	?>
	<div class="wrap">
		<h1>RMU News Settings</h1>
		<form method="POST" action="">
			<?php wp_nonce_field('rmu_news_settings', 'rmu_news_nonce'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">API URL</th>
					<td>
						<input type="url" name="api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text" />
						<p class="description">URL สำหรับดึงข้อมูลข่าว (เช่น https://www.rmu.ac.th/api/posts/filter)</p>
					</td>
				</tr>
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
			<?php submit_button(); ?>
		</form>

		<div class="rmu-news-preview" style="margin-top: 40px; padding: 20px; background: #f9f9f9; border-radius: 8px;">
			<h2>ตัวอย่างการแสดงผล</h2>
			<div class="rmu-news-buttons" style="display: flex; gap: 0; margin: 20px 0;">
				<button type="button" style="
					background-color: <?php echo esc_attr($button_color); ?>; 
					color: <?php echo esc_attr($button_text_color); ?>; 
					border: 1px solid <?php echo esc_attr($button_border_color); ?>; 
					padding: 12px 20px; 
					border-radius: 8px 0 0 8px;
					cursor: default;
				">ทั้งหมด</button>
				<button type="button" style="
					background-color: <?php echo esc_attr($button_hover_color); ?>; 
					color: <?php echo esc_attr($button_hover_text_color); ?>; 
					border: 1px solid <?php echo esc_attr($button_border_color); ?>; 
					padding: 12px 20px; 
					border-left: none;
					cursor: default;
				">ข่าวสมัครงาน (Active/Hover)</button>
				<button type="button" style="
					background-color: <?php echo esc_attr($button_color); ?>; 
					color: <?php echo esc_attr($button_text_color); ?>; 
					border: 1px solid <?php echo esc_attr($button_border_color); ?>; 
					padding: 12px 20px; 
					border-radius: 0 8px 8px 0;
					border-left: none;
					cursor: default;
				">ข่าวประกวดราคา</button>
			</div>
		</div>

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
	</style>

	<script>
		jQuery(document).ready(function ($) {
			// Initialize color picker
			$('.color-field').wpColorPicker({
				change: function (event, ui) {
					// Update preview when color changes
					updatePreview();
				},
				clear: function () {
					// Update preview when color is cleared
					setTimeout(updatePreview, 50);
				}
			});

			function updatePreview() {
				var buttonColor = $('input[name="button_color"]').val();
				var buttonTextColor = $('input[name="button_text_color"]').val();
				var buttonBorderColor = $('input[name="button_border_color"]').val();
				var buttonHoverColor = $('input[name="button_hover_color"]').val();
				var buttonHoverTextColor = $('input[name="button_hover_text_color"]').val();

				var previewButtons = $('.rmu-news-preview .rmu-news-buttons button');

				// Update normal buttons
				previewButtons.eq(0).css({
					'background-color': buttonColor,
					'color': buttonTextColor,
					'border-color': buttonBorderColor
				});

				previewButtons.eq(2).css({
					'background-color': buttonColor,
					'color': buttonTextColor,
					'border-color': buttonBorderColor
				});

				// Update active/hover button
				previewButtons.eq(1).css({
					'background-color': buttonHoverColor,
					'color': buttonHoverTextColor,
					'border-color': buttonBorderColor
				});
			}
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