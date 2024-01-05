<div class="sidebar uk-visible@m">
    <% loop $SideNavigation %>
        <ul class="uk-nav uk-nav-default tm-nav">
            <li class="uk-nav-header">
                $Title
            </li>
            <% loop $Children %>
                <li<% if $Active %> class="uk-active"<% end_if %>><a href="$Link">$Title</a></li>
            <% end_loop %>
        </ul>
    <% end_loop %>
</div>
<div id="my-id" uk-offcanvas>
    <div class="uk-offcanvas-bar">
        <button class="uk-offcanvas-close" type="button" uk-close></button>
        <ul class="uk-nav uk-nav-default tm-nav">
            <% loop $MainNavigation %>
                <li<% if $Active %> class="uk-active"<% end_if %>><a href="$Link">$Title</a></li>
            <% end_loop %>
        </ul>
        <% loop $SideNavigation %>
            <ul class="uk-nav uk-nav-default tm-nav">
                <li class="uk-nav-header">
                    $Title
                </li>
                <% loop $Children %>
                    <li<% if $Active %> class="uk-active"<% end_if %>><a href="$Link">$Title</a></li>
                <% end_loop %>
            </ul>
        <% end_loop %>
    </div>
</div>
