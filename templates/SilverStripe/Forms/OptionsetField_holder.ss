<div id="$HolderID" class="uk-margin<% if $extraClass %> $extraClass<% end_if %>">
        <% if $Title %><label class="uk-form-label">$Title</label><% end_if %>
    <div class="uk-form-controls uk-form-controls-text">
        $Field
    </div>
	<% if $RightTitle %><label class="right">$RightTitle</label><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<% if $Description %><span class="description">$Description</span><% end_if %>
</div>
