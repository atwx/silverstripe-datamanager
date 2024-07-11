<div class="optionset-field">
    <% loop $Options %>
        <label for="$ID" class="uk-form-label">
            <input class="uk-radio" id="$ID" class="radio" name="$Name" type="radio" value="$Value"<% if $isChecked %>
                   checked<% end_if %><% if $isDisabled %> disabled<% end_if %> <% if $Up.Required %>required<% end_if %> />
            $Title
        </label>
    <% end_loop %>
</div>
