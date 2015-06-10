<?php global $post ?><div id="op-le-editor-separator" class="cf"></div>
<div class="op-hidden"><?php echo $content_layouts_dialog.$presets_dialog.op_tpl('live_editor/row_select').op_tpl('live_editor/row_options').op_tpl('live_editor/advanced_element').op_tpl('live_editor/split_column').op_tpl('live_editor/membership').op_tpl('live_editor/typography').op_tpl('live_editor/settings').op_tpl('live_editor/help').op_tpl('live_editor/elements').op_tpl('live_editor/colours').op_tpl('live_editor/headers').$GLOBALS['op_feature_area_dialogs'] ?></div>
<?php echo op_tpl('live_editor/epicbox') ?>
<!-- LiveEditor Header Toolbar-->
<div id="op-le-settings-toolbar" class="op-le-settings-toolbar--sidebar">
    <div id="op-le-settings-toolbar-container" class="op-le-settings-toolbar-container">
        <div class="container">
            <img src="<?php echo OP_IMG ?>logo-liveeditor.png" alt="LiveEditor" id="op-liveeditor-logo" class="op-logo animated flipInY" />
            <h2 class="op-le-settings-toolbar--title"><?php echo $post->post_title; ?></h2>
            <div class="links"><ul>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_layouts_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_layouts_bg.png'" ><a href="#le-headers-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_layouts_bg.png" alt="<?php _e('Layout Settings', OP_SN) ?>" class="animated pulse" /><?php _e('Layout Settings', OP_SN) ?></a></li>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_color_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_color_bg.png'"><a href="#le-colours-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_color_bg.png" alt="<?php _e('Colour Scheme Settings', OP_SN) ?>" class="animated pulse" /><?php _e('Colour Scheme Settings', OP_SN) ?></a></li>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_typography_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_typography_bg.png'" ><a href="#le-typography-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_typography_bg.png" alt="<?php _e('Typography Settings', OP_SN) ?>" class="animated pulse" /><?php _e('Typography Settings', OP_SN) ?></a></li>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_settings_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_settings_bg.png'" alt="<?php _e('Page Settings', OP_SN) ?>"><a href="#le-settings-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_settings_bg.png" alt="<?php _e('Page Settings', OP_SN) ?>" class="animated pulse" /><?php _e('Page Settings', OP_SN) ?></a></li>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_membership_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_membership_bg.png'"><a href="#le-membership-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_membership_bg.png" alt="<?php _e('Membership Settings', OP_SN) ?>" class="animated pulse" /><?php _e('Membership Settings', OP_SN) ?></a></li>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_content_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_content_bg.png'"><a href="#le-layouts-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_content_bg.png" alt="<?php _e('Content Templates', OP_SN) ?>" title="<?php _e('Content Templates', OP_SN) ?>" class="animated pulse" /><?php _e('Content Templates', OP_SN) ?></a></li>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_revisions_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_revisions_bg.png'"><a href="#op-revisions-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_revisions_bg.png" alt="<?php _e('Page Revisions', OP_SN) ?>" title="<?php _e('Page Revisions', OP_SN) ?>" class="animated pulse" /><?php _e('Page Revisions', OP_SN) ?></a></li>
                <li onmouseover="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_help_icon.png'" onmouseout="this.getElementsByTagName('img')[0].src='<?php echo OP_IMG ?>live_editor/le_help_bg.png'" ><a href="#le-help-dialog"><img src="<?php echo OP_IMG ?>live_editor/le_help_bg.png" alt="<?php _e('Help', OP_SN) ?>" class="animated pulse" /><?php _e('Help', OP_SN) ?></a></li>
            </ul></div>
        </div>
        <div id="op-le-toolbar-sidebar" class="op-le-toolbar--sidebar">
            <select name="op[live_editor][status]" id="op-live-editor-status">
                <option value="draft"<?php echo $post->post_status == 'draft' ? ' selected="selected"':'' ?>><?php _e('Draft', OP_SN) ?></option>
                <option value="publish"<?php echo $post->post_status == 'publish' ? ' selected="selected"':'' ?>><?php _e('Publish', OP_SN) ?></option>
            </select>
            <div class="toggle-container" id="toggle-visibility" >
                <img src="<?php echo OP_IMG ?>toggle-visibility.png"  alt="<?php _e('Show/Hide Controls', OP_SN); ?>" class="toggle-visibility animated flash"  />
                <?php _e('Show/Hide Controls', OP_SN); ?>
            </div>
            <div class="link-container">
                <?php
                if ('publish' == $post->post_status) {
                    $previewLink = esc_url(get_permalink($post->ID));
                } else {
                    $previewLink = set_url_scheme(get_permalink($post->ID));
                    $previewLink = esc_url(apply_filters('preview_post_link', add_query_arg('preview', 'true', $previewLink)));
                }
                ?>
                <a class="op-pb-button gray" href="<?php echo $previewLink; ?>" target="_blank"><?php _e('View Public Link', OP_SN); ?></a>
            </div>
            <?php wp_nonce_field( 'op_liveeditor', '_wpnonce', false ) ?>
            <div class="save-options">
                <input type="hidden" name="page_id" id="page_id" value="<?php echo OP_PAGEBUILDER_ID ?>" />
                <button type="button" id="op-save-preset" class="op-pb-button gray"><?php _e('Save As Preset',OP_SN) ?></button>
                <button type="submit" id="op-le-save-2" class="op-pb-button gray"><?php _e('Save &amp; Close',OP_SN) ?></button>
                <button type="submit" id="op-le-save-1" class="op-pb-button green"><?php _e('Save &amp; Continue',OP_SN) ?></button>
            </div>
        </div>
    </div>
    <div class="op-le-toggle-sidebar" id="op-le-toggle-sidebar">
        <a class="op-le-toggle-sidebar-btn" id="op-le-toggle-sidebar-btn"><?php _e('Toogle sidebar', OP_SN); ?></a>
    </div>
</div>
<!-- LiveEditor Footer Toolbar -->
<script type="text/javascript">
    var $ = jQuery;
    $(document).ready(function(){
        $('.toggle-container').click(function(){
            $('.op-row-links, .add-new-row-content, .add-element-container, .add-new-element').stop().slideToggle();
            $('.op-popup').toggleClass('op-popup-clean');
            var $el = $('#toggle-visibility');
            if ($el.css('opacity')==.25) {
                $el.css('opacity', 1);
            } else { $el.css('opacity', 0.25) }
        });
    });
</script>