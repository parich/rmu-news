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
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach (array_keys($manifest_data) as $block_type) {
		register_block_type(__DIR__ . "/build/{$block_type}");
	}
}
add_action('init', 'create_block_rmu_news_block_init');


function rmu_news_enqueue_assets()
{
	wp_enqueue_script(
		'rmu-news-script',
		plugins_url('src/rmu-news/view.js', __FILE__),
		['wp-element', 'wp-components', 'wp-block-editor'],
		filemtime(plugin_dir_path(__FILE__) . 'src/rmu-news/view.js'),
		true
	);

	wp_enqueue_style(
		'rmu-news-style',
		plugins_url('src/rmu-news/style.css', __FILE__),
		[],
		filemtime(plugin_dir_path(__FILE__) . 'src/rmu-news/style.css')
	);
}
add_action('enqueue_block_editor_assets', 'rmu_news_enqueue_assets');

// Register shortcode
function rmu_news_shortcode($atts)
{
	ob_start();
	?>
	<div id="rmu-news-container"></div>
	<?php
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
		update_option('rmu_news_api_url', sanitize_text_field($_POST['api_url']));
		update_option('rmu_news_active_color', sanitize_hex_color($_POST['active_color']));
		update_option('rmu_news_text_color', sanitize_hex_color($_POST['text_color']));
	}

	$api_url = get_option('rmu_news_api_url', 'https://www.rmu.ac.th/api/posts');
	$active_color = get_option('rmu_news_active_color', '#ffffff');
	$text_color = get_option('rmu_news_text_color', '#000000');
	?>
	<div class="wrap">
		<h1>RMU News Settings</h1>
		<form method="POST" action="">
			<table class="form-table">
				<tr valign="top">
					<th scope="row">API URL</th>
					<td><input type="text" name="api_url" value="<?php echo esc_attr($api_url); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Active Color</th>
					<td><input type="text" name="active_color" value="<?php echo esc_attr($active_color); ?>"
							class="color-field" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Text Color</th>
					<td><input type="text" name="text_color" value="<?php echo esc_attr($text_color); ?>"
							class="color-field" /></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}