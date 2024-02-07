<% if $HasSessionMessage %>
<div class="uk-alert-$SessionMessageType" uk-alert>
    <a href class="uk-alert-close" uk-close></a>
    <p>$SessionMessage</p>
</div>
<% end_if %>
