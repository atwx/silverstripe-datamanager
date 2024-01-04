<div class="sidebar uk-visible@m">
    <h3>Documentation</h3>
    <% loop $MainNavigation %>
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
        <h3>Documentation</h3>
        <ul class="uk-nav uk-nav-default tm-nav">
            <li class="uk-nav-header">Getting started</li>
            <li><a href="/docs/introduction">Introduction</a></li>
            <li><a href="/docs/installation">Installation</a></li>
            <li><a href="/docs/less">Less</a></li>
            <li><a href="/docs/sass">Sass</a></li>
            <li><a href="/docs/javascript">JavaScript</a></li>
            <li><a href="/docs/webpack">Webpack</a></li>
            <li><a href="/docs/custom-icons">Custom icons</a></li>
            <li><a href="/docs/avoiding-conflicts">Avoiding conflicts</a></li>
            <li><a href="/docs/accessibility">Accessibility</a></li>
            <li><a href="/docs/rtl">RTL support</a></li>
            <li><a href="/docs/migration">Migration</a></li>
        </ul>
    </div>
</div>