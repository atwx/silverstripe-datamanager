<% if $IncludeFormTag %>
    <form $AttributesHTML>
<% end_if %>
<% if $Message %>
        <p id="{$FormName}_error" class="message $MessageType">$Message</p>
<% else %>
        <p id="{$FormName}_error" class="message $MessageType" style="display: none"></p>
<% end_if %>

    <fieldset class="uk-fieldset uk-grid-small" uk-grid>
        <% if $Legend %>
            <legend>$Legend</legend><% end_if %>
        <% loop $Fields %>
            $SmallFieldHolder
        <% end_loop %>
        <% if $Actions %>
            <% loop $Actions %>
                <div>
                    <label class="uk-form-label">&nbsp;</label>
                    $Field
                </div>
            <% end_loop %>
        <% end_if %>
    </fieldset>

<% if $IncludeFormTag %>
    </form>
<% end_if %>
