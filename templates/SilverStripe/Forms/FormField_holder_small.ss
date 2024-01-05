<div id="$HolderID">
	<% if $Title %><label class="uk-form-label" for="$ID">$Title</label><% end_if %>
	<div class="uk-form-controls">
		$Field
	</div>
	<% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<% if $Description %><span class="description">$Description</span><% end_if %>
</div>
