<div class="uk-flex uk-flex-between">
    <div>
        <h1 class="uk-heading">$Title</h1>
        <p>$Description</p>
    </div>
    <% if $Actions %>
        <div>
            <% loop $Actions %>
                <a class="uk-button-primary uk-button" href="$Link">
                    $Title
                </a>
            <% end_loop %>
        </div>
    <% end_if %>
</div>
