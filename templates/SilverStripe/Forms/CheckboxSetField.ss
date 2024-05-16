<div class="uk-form-controls">
    <% if $Options.Count %>
        <% loop $Options %>
            <input id="$ID" class="uk-checkbox" name="$Name" type="checkbox" value="$Value.ATT"<% if $isChecked %>
                   checked="checked"<% end_if %><% if $isDisabled %> disabled="disabled"<% end_if %> />
            <label for="$ID">$Title</label>
            <br/>
        <% end_loop %>
    <% else %>
        <p><%t SilverStripe\\Forms\\CheckboxSetField_ss.NOOPTIONSAVAILABLE 'No options available' %></p>
    <% end_if %>
</div>
