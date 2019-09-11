const RECENT_ITEMS_COOKIE = 'RECENT';
const MAX_RECENT_ITEMS = 100;

function addRecentItemEventListeners()
{
    var recentItemRemove = jQuery('.recent-item-remove');
    var recentItemsClearAll = jQuery('#recent-items-clear-all');
    var recentItemFlag = jQuery('.recent-item-flag');

    recentItemRemove.click(function ()
    {
        var itemIdentifier = jQuery(this).attr('data-identifier');
        var itemId = jQuery(this).attr('data-id');
        var row = jQuery('#row-' + itemIdentifier);
        jQuery(row).hide("slow");
        removeRecentlyVisitedItem(itemId);
        var itemIds = getRecentItemIdsFromCookie();
        var count = itemIds.length;
        jQuery('#recent-items-count').text(count);
    });

    recentItemsClearAll.click(function ()
    {
        if (confirm('Clear all recently visited items?'))
        {
            removeAllItemsFromCookie();
            jQuery('#recent-items').remove();
            jQuery(this).remove();
            window.location.href = document.location.href;
        }
    });


    recentItemFlag.click(function (e)
    {
        var flag = jQuery(this);
        var itemId = flag.attr('data-id');
        if (flag.hasClass('flagged'))
        {
            removeRecentlyVisitedItem(itemId);
            flag.removeClass('flagged');
        }
        else
        {
            addRecentlyVisitedItem(itemId);
            flag.addClass('flagged');
        }
    });
}

function addRecentlyVisitedItem(idToAdd)
{
    updateCookie('add', idToAdd);
}

function getRecentItemIdsFromCookie()
{
    var cookieValue = Cookies.get(RECENT_ITEMS_COOKIE);
    var itemIds = [];
    if (cookieValue !== undefined && cookieValue.length > 0)
    {
        itemIds = cookieValue.split(',');
    }

    return itemIds;
}

function removeAllItemsFromCookie()
{
    Cookies.remove(RECENT_ITEMS_COOKIE);
}

function removeRecentlyVisitedItem(idToRemove)
{
    updateCookie('remove', idToRemove);
}

function updateCookie(action, itemId)
{
    // Get the current list of items.
    var oldList = getRecentItemIdsFromCookie();

    // Create a new list.
    var newList = [];

    // Put an id to be added as the first element in the list.
    if (action === 'add')
        newList.push(itemId);

    // Copy all the items from the old list to the new list.
    for (id of oldList)
    {
        // Skip the id being added or removed.
        if (itemId === id)
            continue;

        // Add the old id to the new list.
        newList.push(id);

        // Quit after adding the max number of items.
        if (action === 'add' && newList.length >= MAX_RECENT_ITEMS)
            break;
    }

    // Show or hide the currently displayed 'Recent Items' link. This logic also exists in avantsearch-script.php.
    // It is needed there because the search textbox and links are dynamically added to each page when it loads
    // and this logic only works on a page is already loaded.
    var idCount = newList.length;
    var recentItemLink = jQuery('#recent-items-link');
    idCount === 0 ? recentItemLink.hide() : recentItemLink.show();

    if (idCount === 0)
    {
        removeAllItemsFromCookie();

        // If on the Recent Items page, hide links that operate on recent items.
        jQuery('#recent-items-as-search-results').hide();
        jQuery('#recent-items-clear-all').hide();
    }
    else
    {
        Cookies.set(RECENT_ITEMS_COOKIE, newList.join(','), {expires: 14});
    }
}

jQuery(document).ready(function ()
{
    addRecentItemEventListeners();
});
