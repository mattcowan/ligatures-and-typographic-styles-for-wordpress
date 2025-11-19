<?php
/**
 * Admin settings page template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Verify user has permission
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'headline-ligatures-styles'));
}

// Save settings (with proper sanitization)
if (isset($_POST['hls_save_settings']) &&
    check_admin_referer('hls_settings_nonce') &&
    current_user_can('manage_options')) {

    // Use proper sanitization via registered settings
    if (isset($_POST['hls_presets'])) {
        $sanitized = Headline_Ligatures_Styles::get_instance()->sanitize_presets($_POST['hls_presets']);
        update_option('hls_presets', $sanitized);

        // Clear cache
        Headline_Ligatures_Styles::get_instance()->clear_cache();

        echo '<div class="notice notice-success"><p>' .
             esc_html__('Settings saved successfully.', 'headline-ligatures-styles') .
             '</p></div>';
    }
}

$instance = Headline_Ligatures_Styles::get_instance();
$presets = $instance->get_presets();
$custom_fonts = get_option('hls_custom_fonts', array());
?>

<div class="wrap hls-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Skip link for accessibility -->
    <a href="#hls-main-content" class="screen-reader-text skip-link">
        <?php _e('Skip to main content', 'headline-ligatures-styles'); ?>
    </a>

    <div class="hls-admin-container">
        <div class="hls-admin-tabs" role="tablist" aria-label="<?php esc_attr_e('Settings sections', 'headline-ligatures-styles'); ?>">
            <button
                class="hls-tab-button active"
                data-tab="presets"
                role="tab"
                aria-selected="true"
                aria-controls="hls-tab-presets"
                id="hls-tab-button-presets">
                <?php _e('Presets', 'headline-ligatures-styles'); ?>
            </button>
            <button
                class="hls-tab-button"
                data-tab="fonts"
                role="tab"
                aria-selected="false"
                aria-controls="hls-tab-fonts"
                id="hls-tab-button-fonts">
                <?php _e('Custom Fonts', 'headline-ligatures-styles'); ?>
            </button>
            <button
                class="hls-tab-button"
                data-tab="features"
                role="tab"
                aria-selected="false"
                aria-controls="hls-tab-features"
                id="hls-tab-button-features">
                <?php _e('Font Features', 'headline-ligatures-styles'); ?>
            </button>
            <button
                class="hls-tab-button"
                data-tab="help"
                role="tab"
                aria-selected="false"
                aria-controls="hls-tab-help"
                id="hls-tab-button-help">
                <?php _e('Help', 'headline-ligatures-styles'); ?>
            </button>
        </div>

        <!-- Presets Tab -->
        <div
            class="hls-tab-content active"
            id="hls-tab-presets"
            role="tabpanel"
            aria-labelledby="hls-tab-button-presets"
            tabindex="0">
            <h2><?php _e('Typography Presets', 'headline-ligatures-styles'); ?></h2>
            <p><?php _e('Manage your saved font feature combinations. These presets appear in the block editor for quick application.', 'headline-ligatures-styles'); ?></p>

            <div class="hls-preset-controls">
                <?php if (!empty($custom_fonts)): ?>
                <div class="hls-preset-font-selector">
                    <label for="hls-preview-font-select">
                        <?php _e('Preview with Font:', 'headline-ligatures-styles'); ?>
                    </label>
                    <select id="hls-preview-font-select" class="hls-font-select">
                        <option value=""><?php _e('Default (system font)', 'headline-ligatures-styles'); ?></option>
                        <?php
                        foreach ($custom_fonts as $font) {
                            if (!empty($font['font_faces'])) {
                                $families = array_unique(array_map(function($face) {
                                    return $face['family'];
                                }, $font['font_faces']));

                                foreach ($families as $family) {
                                    echo '<option value="' . esc_attr($family) . '">' . esc_html($family) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                    <p class="description">
                        <?php _e('Select a custom font to preview how the presets will look with that font.', 'headline-ligatures-styles'); ?>
                    </p>
                </div>
                <?php endif; ?>

                <div class="hls-preset-size-control">
                    <label for="hls-preview-size-slider">
                        <?php _e('Preview Size:', 'headline-ligatures-styles'); ?>
                        <span id="hls-preview-size-value" class="hls-size-value">50px</span>
                    </label>
                    <input
                        type="range"
                        id="hls-preview-size-slider"
                        class="hls-size-slider"
                        min="12"
                        max="96"
                        value="50"
                        step="1"
                        aria-label="<?php esc_attr_e('Adjust preview text size', 'headline-ligatures-styles'); ?>"
                        aria-valuemin="12"
                        aria-valuemax="96"
                        aria-valuenow="50"
                        aria-valuetext="50 pixels" />
                    <p class="description">
                        <?php _e('Adjust the size of the preview text to better see typography features.', 'headline-ligatures-styles'); ?>
                    </p>
                </div>
            </div>

            <div class="hls-presets-grid">
                <?php foreach ($presets as $preset): ?>
                <div class="hls-preset-card">
                    <h3><?php echo esc_html($preset['name']); ?></h3>
                    <p class="hls-preset-description"><?php echo esc_html($preset['description']); ?></p>
                    <div class="hls-preset-features">
                        <strong><?php _e('Features:', 'headline-ligatures-styles'); ?></strong>
                        <?php echo esc_html(implode(', ', $preset['features'])); ?>
                    </div>
                    <div class="hls-preset-preview" style="font-feature-settings: <?php echo esc_attr($instance->features_to_css($preset['features'])); ?>">
                        <?php _e('Elegant Typography', 'headline-ligatures-styles'); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="hls-add-preset-section">
                <h3><?php _e('Add Custom Preset', 'headline-ligatures-styles'); ?></h3>
                <p><?php _e('Custom presets can be created directly in the block editor by selecting features and clicking "Save as Preset".', 'headline-ligatures-styles'); ?></p>
            </div>
        </div>

        <!-- Fonts Tab -->
        <div
            class="hls-tab-content"
            id="hls-tab-fonts"
            role="tabpanel"
            aria-labelledby="hls-tab-button-fonts"
            hidden="hidden"
            tabindex="0">
            <h2><?php _e('Custom Fonts', 'headline-ligatures-styles'); ?></h2>
            <p><?php _e('Upload webfont kits (MyFonts, Fontspring, etc.) to use custom fonts with OpenType features. Once uploaded, fonts will be available in the block editor.', 'headline-ligatures-styles'); ?></p>


            <?php if (!empty($custom_fonts)): ?>
            <div class="hls-fonts-list">
                <h3><?php _e('Uploaded Fonts', 'headline-ligatures-styles'); ?></h3>
                <?php foreach ($custom_fonts as $font): ?>
                <div class="hls-font-card">
                    <div class="hls-font-header">
                        <h4><?php echo esc_html($font['name']); ?></h4>
                        <button
                            class="button hls-delete-font"
                            data-font-id="<?php echo esc_attr($font['id']); ?>"
                            data-font-name="<?php echo esc_attr($font['name']); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('Delete font kit: %s', 'headline-ligatures-styles'), $font['name'])); ?>">
                            <span aria-hidden="true" class="dashicons dashicons-trash"></span>
                            <?php _e('Delete', 'headline-ligatures-styles'); ?>
                        </button>
                    </div>
                    <div class="hls-font-families">
                        <strong><?php _e('Font Families:', 'headline-ligatures-styles'); ?></strong>
                        <?php
                        if (!empty($font['font_faces'])) {
                            $families = array_unique(array_map(function($face) {
                                return $face['family'];
                            }, $font['font_faces']));
                            echo esc_html(implode(', ', $families));
                        }
                        ?>
                    </div>
                    <div class="hls-font-meta">
                        <small>
                            <?php
                            printf(__('Uploaded: %s', 'headline-ligatures-styles'), esc_html($font['uploaded_date']));
                            if (!empty($font['file_count'])) {
                                echo ' &bull; ';
                                printf(_n('%d font file', '%d font files', $font['file_count'], 'headline-ligatures-styles'), $font['file_count']);
                            }
                            ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="hls-empty-state" role="status">
                <p><strong><?php _e('No custom fonts uploaded yet.', 'headline-ligatures-styles'); ?></strong></p>
                <p><?php _e('Upload a webfont kit using the form below to add custom fonts with OpenType features.', 'headline-ligatures-styles'); ?></p>
            </div>
            <?php endif; ?>

            <div class="hls-upload-font-section" id="hls-upload-font-section">
                <h3><?php _e('Upload Font Kit', 'headline-ligatures-styles'); ?></h3>
                <p><?php _e('Upload a complete webfont kit as a ZIP file (e.g., MyWebfontsKit.zip). The ZIP should contain the CSS file and all font files.', 'headline-ligatures-styles'); ?></p>

                <div class="hls-upload-form">
                    <div class="hls-form-field">
                        <label for="hls-font-name">
                            <?php _e('Font Kit Name:', 'headline-ligatures-styles'); ?>
                            <span class="required" aria-label="<?php esc_attr_e('required', 'headline-ligatures-styles'); ?>">*</span>
                        </label>
                        <input
                            type="text"
                            id="hls-font-name"
                            name="hls-font-name"
                            class="regular-text"
                            placeholder="<?php esc_attr_e('e.g., MyFonts Kit 2024', 'headline-ligatures-styles'); ?>"
                            aria-required="true"
                            aria-describedby="hls-font-name-desc"
                            required />
                        <p id="hls-font-name-desc" class="description">
                            <?php _e('Enter a descriptive name for this font kit', 'headline-ligatures-styles'); ?>
                        </p>
                    </div>

                    <div class="hls-form-field">
                        <label for="hls-font-file">
                            <?php _e('ZIP File:', 'headline-ligatures-styles'); ?>
                        </label>
                        <label for="hls-font-file" class="screen-reader-text">
                            <?php _e('Choose ZIP file containing webfont kit', 'headline-ligatures-styles'); ?>
                        </label>
                        <div class="hls-upload-method-buttons">
                            <button type="button" id="hls-select-file-btn" class="button">
                                <span class="dashicons dashicons-upload" aria-hidden="true"></span>
                                <?php _e('Choose ZIP File', 'headline-ligatures-styles'); ?>
                            </button>
                        </div>
                        <input
                            type="file"
                            id="hls-font-file"
                            name="hls-font-file"
                            accept=".zip"
                            aria-describedby="hls-file-instructions"
                            style="display: none;" />
                        <span id="hls-file-instructions" class="screen-reader-text">
                            <?php _e('Upload a webfont kit as a ZIP file. The ZIP should contain CSS file and font files.', 'headline-ligatures-styles'); ?>
                        </span>
                        <div id="hls-selected-file" class="hls-selected-file" style="display: none;">
                            <span class="dashicons dashicons-media-archive" aria-hidden="true"></span>
                            <span id="hls-file-name"></span>
                            <span id="hls-file-size" class="hls-file-size"></span>
                            <button type="button" id="hls-clear-file-btn" class="button-link" aria-label="<?php esc_attr_e('Clear selected file', 'headline-ligatures-styles'); ?>">
                                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>

                    <button type="button" id="hls-upload-font-btn" class="button button-primary" disabled>
                        <?php _e('Upload Font Kit', 'headline-ligatures-styles'); ?>
                    </button>
                    <div id="hls-upload-progress" class="hls-upload-progress" style="display: none;">
                        <div
                            class="hls-progress-bar"
                            role="progressbar"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="0"
                            aria-labelledby="hls-progress-label">
                            <div class="hls-progress-fill" style="width: 0%;"></div>
                        </div>
                        <div id="hls-progress-label" class="hls-progress-text" role="status" aria-live="polite">
                            <?php _e('Uploading...', 'headline-ligatures-styles'); ?>
                        </div>
                    </div>
                    <div id="hls-font-message" role="alert" aria-live="assertive" aria-atomic="true" style="margin-top: 10px;"></div>
                </div>

                <div class="hls-font-help">
                    <h4><?php _e('How to use:', 'headline-ligatures-styles'); ?></h4>
                    <ol>
                        <li><?php _e('Download your webfont kit from MyFonts, Fontspring, or another provider', 'headline-ligatures-styles'); ?></li>
                        <li><?php _e('If the kit is not already zipped, create a ZIP file containing the entire kit folder (including CSS file and all font files in their directories)', 'headline-ligatures-styles'); ?></li>
                        <li><?php _e('Click "Choose ZIP File" and select your webfont kit ZIP file', 'headline-ligatures-styles'); ?></li>
                        <li><?php _e('Give your kit a descriptive name and click "Upload Font Kit"', 'headline-ligatures-styles'); ?></li>
                        <li><?php _e('The plugin will extract the ZIP, process the fonts, and make them available in the block editor', 'headline-ligatures-styles'); ?></li>
                    </ol>
                    <p><strong><?php _e('What should the ZIP contain:', 'headline-ligatures-styles'); ?></strong></p>
                    <ul>
                        <li><?php _e('A CSS file with @font-face declarations (e.g., MyWebfontsKit.css)', 'headline-ligatures-styles'); ?></li>
                        <li><?php _e('Font files in their subdirectories (e.g., webFonts/FontName/font.woff2)', 'headline-ligatures-styles'); ?></li>
                        <li><?php _e('The directory structure must match the paths in the CSS file', 'headline-ligatures-styles'); ?></li>
                    </ul>
                    <p><strong><?php _e('Note:', 'headline-ligatures-styles'); ?></strong> <?php _e('The plugin automatically rewrites CSS paths and stores all files in your WordPress uploads directory. All fonts and their files will be properly organized and served from your server.', 'headline-ligatures-styles'); ?></p>
                </div>
            </div>
        </div>

        <!-- Features Tab -->
        <div
            class="hls-tab-content"
            id="hls-tab-features"
            role="tabpanel"
            aria-labelledby="hls-tab-button-features"
            hidden="hidden"
            tabindex="0">
            <h2><?php _e('Available OpenType Features', 'headline-ligatures-styles'); ?></h2>
            <p><?php _e('This plugin supports the following OpenType font features. Note that not all fonts include all features.', 'headline-ligatures-styles'); ?></p>

            <div class="hls-features-list">
                <div class="hls-feature-category">
                    <h3><?php _e('Ligatures', 'headline-ligatures-styles'); ?></h3>
                    <table class="widefat">
                        <caption class="screen-reader-text">
                            <?php _e('Available ligature OpenType features', 'headline-ligatures-styles'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col"><?php _e('Code', 'headline-ligatures-styles'); ?></th>
                                <th scope="col"><?php _e('Name', 'headline-ligatures-styles'); ?></th>
                                <th scope="col"><?php _e('Description', 'headline-ligatures-styles'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row"><code lang="en">liga</code></th>
                                <td><?php _e('Standard Ligatures', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Common letter combinations like fi, fl, ff', 'headline-ligatures-styles'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><code lang="en">dlig</code></th>
                                <td><?php _e('Discretionary Ligatures', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Optional decorative ligatures for special effects', 'headline-ligatures-styles'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><code lang="en">calt</code></th>
                                <td><?php _e('Contextual Alternates', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Context-aware letter forms that adapt to surrounding characters', 'headline-ligatures-styles'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="hls-feature-category">
                    <h3><?php _e('Stylistic Sets', 'headline-ligatures-styles'); ?></h3>
                    <table class="widefat">
                        <caption class="screen-reader-text">
                            <?php _e('Available stylistic set OpenType features', 'headline-ligatures-styles'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col"><?php _e('Code', 'headline-ligatures-styles'); ?></th>
                                <th scope="col"><?php _e('Name', 'headline-ligatures-styles'); ?></th>
                                <th scope="col"><?php _e('Description', 'headline-ligatures-styles'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <tr>
                                <th scope="row"><code lang="en">ss<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></code></th>
                                <td><?php printf(__('Stylistic Set %d', 'headline-ligatures-styles'), $i); ?></td>
                                <td><?php _e('Alternate character designs (font-specific)', 'headline-ligatures-styles'); ?></td>
                            </tr>
                            <?php endfor; ?>
                            <tr>
                                <td colspan="3"><em><?php _e('...and ss06 through ss20', 'headline-ligatures-styles'); ?></em></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="hls-feature-category">
                    <h3><?php _e('Swashes & Alternates', 'headline-ligatures-styles'); ?></h3>
                    <table class="widefat">
                        <caption class="screen-reader-text">
                            <?php _e('Available swashes and alternates OpenType features', 'headline-ligatures-styles'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col"><?php _e('Code', 'headline-ligatures-styles'); ?></th>
                                <th scope="col"><?php _e('Name', 'headline-ligatures-styles'); ?></th>
                                <th scope="col"><?php _e('Description', 'headline-ligatures-styles'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row"><code lang="en">swsh</code></th>
                                <td><?php _e('Swashes', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Decorative flourishes and ornamental strokes', 'headline-ligatures-styles'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><code lang="en">cswh</code></th>
                                <td><?php _e('Contextual Swashes', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Context-aware decorative flourishes', 'headline-ligatures-styles'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><code lang="en">salt</code></th>
                                <td><?php _e('Stylistic Alternates', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Alternative character forms', 'headline-ligatures-styles'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><code lang="en">titl</code></th>
                                <td><?php _e('Titling', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Forms optimized for large display sizes', 'headline-ligatures-styles'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><code lang="en">ornm</code></th>
                                <td><?php _e('Ornaments', 'headline-ligatures-styles'); ?></td>
                                <td><?php _e('Decorative ornaments and symbols', 'headline-ligatures-styles'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Help Tab -->
        <div
            class="hls-tab-content"
            id="hls-tab-help"
            role="tabpanel"
            aria-labelledby="hls-tab-button-help"
            hidden="hidden"
            tabindex="0">
            <h2><?php _e('How to Use', 'headline-ligatures-styles'); ?></h2>

            <div class="hls-help-section">
                <h3><?php _e('Basic Usage', 'headline-ligatures-styles'); ?></h3>
                <ol>
                    <li><?php _e('Create or edit a heading block (H1-H6) in the block editor', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Type your headline text', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Select the text you want to style', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Click the "Typography Features" button in the toolbar (icon with decorative "A")', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Choose a preset or select individual features', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('See the live preview and save', 'headline-ligatures-styles'); ?></li>
                </ol>
            </div>

            <div class="hls-help-section">
                <h3><?php _e('Best Fonts for Advanced Typography', 'headline-ligatures-styles'); ?></h3>
                <p><?php _e('This plugin works best with fonts that support OpenType features. Recommended script fonts:', 'headline-ligatures-styles'); ?></p>
                <ul>
                    <li><strong>Calgary Script</strong> <?php _e('by Alejandro Paul - Elegant connecting script', 'headline-ligatures-styles'); ?></li>
                    <li><strong>Affair</strong> <?php _e('by Alejandro Paul - Romantic calligraphy', 'headline-ligatures-styles'); ?></li>
                    <li><strong>Adios Script</strong> <?php _e('by Alejandro Paul - Casual handwritten style', 'headline-ligatures-styles'); ?></li>
                    <li><strong>Parfumerie Script</strong> <?php _e('by Alejandro Paul - Vintage commercial script', 'headline-ligatures-styles'); ?></li>
                </ul>
                <p><?php _e('Load your fonts using @font-face in your theme or a plugin like Adobe Fonts or Google Fonts.', 'headline-ligatures-styles'); ?></p>
            </div>

            <div class="hls-help-section">
                <h3><?php _e('Tips for Script Fonts', 'headline-ligatures-styles'); ?></h3>
                <ul>
                    <li><?php _e('Enable "Contextual Alternates" (calt) for natural letter connections', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Use swashes sparingly on first or last letters only', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Try different stylistic sets to find the best look', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Test your headlines at the actual display size', 'headline-ligatures-styles'); ?></li>
                    <li><?php _e('Not all fonts support all features - experiment!', 'headline-ligatures-styles'); ?></li>
                </ul>
            </div>

            <div class="hls-help-section">
                <h3><?php _e('Technical Notes', 'headline-ligatures-styles'); ?></h3>
                <p><?php _e('This plugin applies CSS font-feature-settings to selected text using inline styles. The features are stored as data attributes on span elements within your content.', 'headline-ligatures-styles'); ?></p>
                <p><?php _e('Browser support: All modern browsers support OpenType features. Internet Explorer 10+ has partial support.', 'headline-ligatures-styles'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- All CSS and JavaScript have been moved to separate external files:
     - assets/css/admin-page.css (or admin-page.min.css)
     - assets/js/admin-page.js (or admin-page.min.js)
     These files are enqueued in the main plugin file via enqueue_admin_assets() method
-->
