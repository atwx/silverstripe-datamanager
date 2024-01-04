<% if $ItemList.MoreThanOnePage %>
<ul class="uk-pagination" uk-margin>
    <% if $ItemList.NotFirstPage %>
    <li><a href="$ItemList.PrevLink"><span uk-pagination-previous></span></a></li>
    <% end_if %>
    <% loop $ItemList.PaginationSummary %>
    <% if $CurrentBool %>
        <li class="uk-active"><span>$PageNum</span></li>
    <% else %>
        <% if $Link %>
    <li><a href="$Link">$PageNum</a></li>
            <% else %>
            ...
            <% end_if %>
    <% end_if %>
        <% end_loop %>
    <% if $ItemList.NotLastPage %>
    <li><a href="$ItemList.NextLink"><span uk-pagination-next></span></a></li>
    <% end_if %>
</ul>
<% end_if %>
