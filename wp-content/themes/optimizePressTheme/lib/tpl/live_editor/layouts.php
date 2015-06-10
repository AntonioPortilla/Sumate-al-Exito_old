<form id="le-layouts-dialog">
	<h1><?php _e('Content Templates',OP_SN) ?></h1>
    <div class="op-lightbox-content">
    	<div class="op-actual-lightbox-content cf">
			<?php
            $tabs = array(
                'module_name' => 'content_layouts',
                'tabs' => array(
                    'predefined' => array(
                        'title' => __('Predefined Templates',OP_SN),
                        'li_class' => 'op-bsw-grey-panel-tabs-selected',
                    ),
                    'upload' => __('Upload Template',OP_SN),
					'export' => __('Export Template',OP_SN),
                ),
                'tab_content' => array(
                    'predefined' => $content_layouts.($content_layout_category_count > 0?op_tpl('live_editor/layouts/keep_options'):''),
                    'upload' => '<iframe src="'.menu_page_url(OP_SN.'-page-builder',false).'&amp;section=content_upload" width="700" height="400"></iframe>',
                    //'upload' => op_tpl('live_editor/layouts/upload'),
                    'export' => op_tpl('live_editor/layouts/export')
                )
            );
            echo op_tpl('generic/tabbed_module',$tabs);
            ?>
        </div>
    </div>
    <div class="op-insert-button cf">
            <button type="submit" class="editor-button"><span><?php _e('Update',OP_SN) ?></span></button>
    </div>
</form>