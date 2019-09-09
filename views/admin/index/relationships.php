<?php

function emitItemInformation(Omeka_Record_AbstractRecord $item)
{
    $type = ItemMetadata::getElementTextForElementName($item, 'Type');
    $subject = ItemMetadata::getElementTextForElementName($item, 'Subject');
    $title = ItemMetadata::getItemTitle($item);

    $html = '<div id="relationships-editor-grid">';
    $imageUrl = ItemPreview::getImageUrl($item, true, true);
    $html .= "<img class='relationships-editor-image' src='$imageUrl'>";

    $html .= "<div class='relationships-editor-metadata'>";
    $html .= "<div class='relationships-editor-title'>$title</div>";
    $html .= "<div><span class='element-name'>Type:</span> $type</div>";
    $html .= "<div><span class='element-name'>Subject:</span> $subject</div>";
    $html .= "</div>";

    $html .= "<div class='relationships-editor-buttons'>";
    $viewLink = html_escape(admin_url('items/show/' . metadata('item', 'id')));
    $html .= "<a href='$viewLink' class='big beige button'>" . __('View Admin Item') . "</a>";
    $publicLink = html_escape(public_url('items/show/' . metadata('item', 'id')));
    $html .= "<div><a href='$publicLink' class='big blue button'>" . __('View Public Page') . "</a></div>";
    if (is_allowed($item, 'edit'))
    {
        $editLink = link_to_item(__('Edit Item'), array('class' => 'big green button'), 'edit');
        $html .= $editLink;
    }
    $html .= "</div>";

    $html .= "</div>";

    echo $html;
}

$item = get_record_by_id('Item', $itemId);
if (empty($item))
{
    echo "No item found";
    return;
}
set_current_record('Item', $item);

$primaryItemIdentifier = ItemMetadata::getItemIdentifier($item);
$itemTitle = __('Edit Relationships for Item %s', $primaryItemIdentifier);
echo head(array('title' => $itemTitle, 'bodyclass'=>'relationships'));

emitItemInformation($item);

$relatedItemsModel = new RelatedItemsModel($item, $this);
$relatedItems = $relatedItemsModel->getRelatedItems();
$relatedItemsEditor = new RelatedItemsEditor($relatedItemsModel, $item);
$formSelectRelationshipNames = $relatedItemsEditor->getRelationshipNamesSelectList();

?>

<table class="relationships-editor-table">
    <thead>
    <tr>
        <th class="relationship-table-relationship"><?php echo __('Relationship'); ?></th>
        <th class="relationship-table-related-item"><?php echo __('Related&nbsp;Item'); ?></th>
        <th class="relationship-table-related-item-title"><?php echo __('Related Item Title'); ?></th>
        <th class="relationship-table-action"><?php echo __('Action'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($relatedItems as $relatedItem)
    {
        $relatedItemIdentifier = $relatedItem->getIdentifier();
        ?>
        <tr id="<?php echo $relatedItem->getRelationshipId(); ?>">
            <td><?php echo $relatedItem->getRelationshipName(); ?></td>
            <td><?php echo $relatedItemIdentifier; ?></td>
            <td><?php echo RelatedItemsEditor::getRelatedItemLink($relatedItemIdentifier) ?></td>
            <td>
                <button type="button" class="action-button edit-relationship-button"><?php echo __('Edit'); ?></button>
                <button type="button" class="action-button remove-relationship-button red button"><?php echo __('X'); ?></button>
            </td>
        </tr>
    <?php }; ?>
    <tr class="add-relationship-row">
        <td><?php echo get_view()->formSelect('relationship-type-code', null, array('multiple' => false), $formSelectRelationshipNames); ?></td>
        <td><?php echo get_view()->formText('related-item-identifier', null, array('size' => 5)); ?></td>
        <td></td>
        <td>
            <button type="button" class="action-button add-relationship-button"><?php echo __('Add'); ?></button>
            <button type="button" class="action-button edit-relationship-button"><?php echo __('Edit'); ?></button>
            <button type="button" class="action-button remove-relationship-button red button"><?php echo __('X'); ?></button>
        </td>
    </tr>
    </tbody>
</table>

<?php
echo '<div id="relationship-editor-speed-bar">Save typing by choosing Relationships or Items from the lists below</div>';
echo '<div id="relationship-editor-recents">';

// Emit an empty list of recent relationships. The client-side Javascript populates it.
echo '<div id="recent-relationships-section">';
echo '<div class="recent-relationships-title">' . __('Recent Relationships') . '</div>';
echo '<div id="recent-relationships"></div>';
echo '</div>'; // recent-relationships-section

// Emit a list of recently visited items.
$recentlyViewedItems = AvantCommon::getRecentlyViewedItems($primaryItemIdentifier);
$clearAll = count($recentlyViewedItems) == 0 ? '' : "<a class='recent-items-clear-all'>" . __('Clear all') . '</a>';
echo '<div id="recent-items-section">';
echo '<div class="recent-items-title">' . __('Recent Items') . $clearAll . '</div>';
echo '<div id="recent-items">';

foreach ($recentlyViewedItems as $recentItemId => $recentItemIdentifier)
{
    $recentItem = ItemMetadata::getItemFromId($recentItemId);
    $recentImageUrl = ItemPreview::getImageUrl($recentItem, true, true);
    if (empty($recentImageUrl))
        $recentImageUrl = ItemPreview::getFallbackImageUrl($recentItem);
    $thumbnail = "<img src='$recentImageUrl'>";

    $title = ItemMetadata::getItemTitle($recentItem);

    $type = ItemMetadata::getElementTextForElementName($recentItem, 'Type');
    $subject = ItemMetadata::getElementTextForElementName($recentItem, 'Subject');
    $metadata = "<div class='recent-item-metadata'><span>Type:</span>$type&nbsp;&nbsp;&nbsp;&nbsp;<span>Subject:</span>$subject</div>";

    $removeTooltip = __('Remove item from this list (does not delete the item)');
    $removeLink = " | <a class='recent-item-remove' data-id='$recentItemId' data-identifier='$recentItemIdentifier' title='$removeTooltip'>" . __('Remove') . '</a>';
    $addButton = "<button type='button' class='action-button recent-item-add' data-identifier='$recentItemIdentifier'>" . __('Add') . "</button>";

    echo "<div id='row-$recentItemIdentifier' class='recent-item-row'>";
    echo "<div class='recent-item-thumbnail' data-identifier='$recentItemIdentifier'>$thumbnail</div>";
    echo "<div class='recent-item'>";
    echo "<div class='recent-item-identifier' data-identifier='$recentItemIdentifier'>$recentItemIdentifier$metadata</div>";
    echo "<div class='recent-item-title'>$addButton$title</div>";
    echo AvantCommon::emitAdminLinksHtml($recentItemId, '', false, $removeLink);
    echo '</div>'; // recent-item
    echo '</div>'; // recent-item-row
}

echo '</div>'; // recent-items
echo '</div>'; // recent-items-section

echo '</div>'; // relationship-editor-recents
?>

<?php
    $relationshipNames = json_encode($formSelectRelationshipNames);
    echo $this->partial('/edit-relationships-script.php', array('primaryItemIdentifier' => $primaryItemIdentifier, 'relationshipNames' => $relationshipNames));
?>
