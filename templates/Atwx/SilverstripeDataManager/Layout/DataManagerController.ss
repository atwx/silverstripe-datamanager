<% include Atwx\\SilverstripeDataManager\\Includes\\Heading Title=$Title, Description=$Description, Actions=$Actions%>
$FilterForm
<table class="uk-table uk-table-striped">
    <thead>
    <tr>
        <% loop $ManagementFields %>
            <th>$Title</th>
        <% end_loop %>
    </tr>
    </thead>
    <tbody>
    <% if $Items.Count > 0 %>
        <% loop $Items %>
            <tr>
                <% loop $ManagementData %>
                    <% if $IsFirst %>
                    <td><a href="$Top.Link("view")/$Up.ID">$Value</a></td>
                    <% else %>
                    <td>$Value</td>
                    <% end_if %>
                <% end_loop %>
                <td>
                    <a href="$Top.Link("edit")/$ID" class="uk-button uk-button-small uk-button-primary">Bearbeiten</a>
                </td>
                <td>
                    <a href="$Top.Link("delete")/$ID?BackURL=$Top.Link" class="button small hollow delete" onclick="return confirm('Sind Sie sicher?')">Löschen</a>
                </td>
            </tr>
        <% end_loop %>
    <% else %>
        <tr>
            <td><p>- Keine Einträge gefunden -</p></td>
        </tr>
    <% end_if %>
    </tbody>
</table>
<% include Atwx\SilverstripeDataManager\Includes\Pagination ItemList=$Items %>
