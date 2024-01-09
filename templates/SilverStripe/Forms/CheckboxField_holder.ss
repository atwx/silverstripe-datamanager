<div id="$HolderID" class="uk-margin">
	<div class="uk-form-controls">
        <label for="$ID">
		$Field
        $Title</label>
	</div>
	<% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
	<% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
	<% if $Description %><span class="description">$Description</span><% end_if %>
</div>
