<?php
$this->addCSS($this->getStylesFileName('photos'));

if( $ctype['options']['list_show_filter'] ) {
    $this->renderAsset('ui/filter-panel', array(
        'css_prefix'   => $ctype['name'],
        'page_url'     => $page_url,
        'fields'       => $fields,
        'props_fields' => $props_fields,
        'props'        => $props,
        'filters'      => $filters,
        'ext_hidden_params' => $ext_hidden_params,
        'is_expanded'  => $ctype['options']['list_expand_filter']
    ));
}
?>

<?php if ($items){ ?>

    <div class="content_list tiled <?php echo $ctype['name']; ?>_list">

        <?php $columns = 3; $index = 1; ?>

        <?php foreach($items as $item){ ?>

            <?php $stop = 0; ?>

            <div class="tile content_list_item <?php echo $ctype['name']; ?>_list_item">

                <div class="photo">
                    <div class="note">
                        <?php echo html_spellcount($item['photos_count'], LANG_PHOTOS_PHOTO_SPELLCOUNT); ?>
						<?php if ($item['is_public'] && !empty($fields['is_public']['is_in_list'])) { ?>
							/ <span><?php echo LANG_PHOTOS_PUBLIC_ALBUM; ?></span>
						<?php } ?>
                    </div>
                    <?php if (!empty($item['is_private_item'])) { ?>
                        <?php echo html_image(default_images('private', $ctype['photos_options']['preset_small']), $ctype['photos_options']['preset_small'], $item['title']); ?>
                    <?php } else { ?>
                        <a href="<?php echo href_to($ctype['name'], $item['slug'].'.html'); ?>" <?php if (!empty($item['cover_image']) && !empty($fields['cover_image']['is_in_list'])){ ?>style="background-image: url(<?php echo html_image_src($item['cover_image'], $ctype['photos_options']['preset_small'], true); ?>);"<?php } ?>>
                            <?php if (!empty($item['cover_image']) && !empty($fields['cover_image']['is_in_list'])){ ?>
                                <?php echo html_image($item['cover_image'], $ctype['photos_options']['preset_small'], $item['title']); ?>
                            <?php } ?>
                            <div class="photos_album_title_wrap">
                                <?php if (!empty($fields['title']['is_in_list'])) { ?>
                                    <div class="clear">
                                        <div class="photos_album_title">
                                            <?php if ($item['parent_id']){ ?>
                                                <?php echo htmlspecialchars($item['parent_title']); ?> &rarr;
                                            <?php } ?>

                                            <?php if (!empty($item['is_private_item'])) { ?>
                                                <?php html($item['title']); ?> <span class="is_private" title="<?php html($item['private_item_hint']); ?>"></span>
                                            <?php } else { ?>
                                                <?php html($item['title']); ?>
                                                <?php if ($item['is_private']) { ?>
                                                    <span class="is_private" title="<?php html(LANG_PRIVACY_HINT); ?>"></span>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($item['fields']['content'])) { ?>
                                    <div class="photos_album_description_wrap">
                                        <div class="photos_album_description">
                                            <?php echo $item['fields']['content']['html']; ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </a>
                    <?php } ?>
                    <?php unset($item['fields']['cover_image'], $item['fields']['content'], $item['fields']['is_public'], $item['fields']['title']); ?>
                </div>

                <div class="fields">

                <?php foreach($item['fields'] as $field){ ?>

                    <?php if ($stop === 2) { break; } ?>

                    <div class="field ft_<?php echo $field['type']; ?> f_<?php echo $field['name']; ?>">

                        <?php if ($field['label_pos'] != 'none'){ ?>
                            <div class="title_<?php echo $field['label_pos']; ?>">
                                <?php echo $field['title'] . ($field['label_pos']=='left' ? ': ' : ''); ?>
                            </div>
                        <?php } ?>

                        <div class="value">
                            <?php if (!empty($item['is_private_item'])) { $stop++; ?>
                                <div class="private_field_hint"><?php echo $item['private_item_hint']; ?></div>
                            <?php } else { ?>
                                 <?php echo $field['html']; ?>
                            <?php } ?>
                        </div>

                    </div>

                <?php } ?>

                </div>

                <?php if ($ctype['is_tags'] && !empty($ctype['options']['is_tags_in_list']) &&  $item['tags']){?>
                    <div class="tags_bar">
                        <?php echo html_tags_bar($item['tags']); ?>
                    </div>
                <?php } ?>

                <?php
                    $show_bar = !empty($item['rating_widget']) ||
                                $fields['date_pub']['is_in_list'] ||
                                $fields['user']['is_in_list'] ||
                                ($ctype['is_comments'] && $item['is_comments_on']) ||
                                !$item['is_approved'];
                ?>

                <?php if ($show_bar){ ?>
                    <div class="info_bar">
                        <?php if (!empty($item['rating_widget'])){ ?>
                            <div class="bar_item bi_rating">
                                <?php echo $item['rating_widget']; ?>
                            </div>
                        <?php } ?>
                        <?php if ($fields['user']['is_in_list']){ ?>
                            <div class="bar_item bi_user" title="<?php echo $fields['user']['title']; ?>">
                                <?php echo $fields['user']['handler']->parse( $item['user'] ); ?>
                            </div>
                        <?php } ?>
                        <?php if ($ctype['is_comments'] && $item['is_comments_on']){ ?>
                            <div class="bar_item bi_comments">
                                <?php if (!empty($item['is_private_item'])) { ?>
                                    <?php echo intval($item['comments']); ?>
                                <?php } else { ?>
                                    <a href="<?php echo href_to($ctype['name'], $item['slug'].'.html'); ?>#comments" title="<?php echo LANG_COMMENTS; ?>">
                                        <?php echo intval($item['comments']); ?>
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <?php if ($fields['date_pub']['is_in_list']){ ?>
                            <div class="bar_item bi_date" title="<?php echo $fields['date_pub']['title']; ?>">
                                <?php echo $fields['date_pub']['handler']->parse($item['date_pub']); ?>
                            </div>
                        <?php } ?>
                        <?php if (!$item['is_approved']){ ?>
                            <div class="bar_item bi_not_approved">
                                <?php echo !empty($item['is_draft']) ? LANG_CONTENT_DRAFT_NOTICE : LANG_CONTENT_NOT_APPROVED; ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

            </div>

            <?php if ($index % $columns == 0) { ?>
                <div class="clear"></div>
            <?php } ?>

        <?php $index++; } ?>

    </div>

    <?php if ($perpage < $total) { ?>
        <?php echo html_pagebar($page, $perpage, $total, $page_url, array_merge($filters, $ext_hidden_params)); ?>
    <?php } ?>

<?php  } else {

    if(!empty($ctype['labels']['many'])){
        echo sprintf(LANG_TARGET_LIST_EMPTY, $ctype['labels']['many']);
    } else {
        echo LANG_LIST_EMPTY;
    }

}
