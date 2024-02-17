<div class="uk-flex uk-flex-between">
    <div>
        <h1 class="uk-heading">$Title</h1>
        <p>$Description</p>
    </div>
    <% if $Actions %>
        <div>
            <% loop $Actions %>
                <a class="uk-button uk-button-small<% if $Primary %> uk-button-primary<% end_if %>"
                   href="$Link"
                   <% if $Target %>target="$Target"<% end_if %>
                   <% if $AccessKey %>accesskey="n"<% end_if %>
                >
                    $Title
                </a>
            <% end_loop %>
        </div>
    <% end_if %>
</div>
