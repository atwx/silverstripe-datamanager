<div class="data-manager">
    <% include Atwx\\SilverstripeDataManager\\Includes\\Heading Title=$Title, Description=$Description, Actions=$Actions%>
    <% include Atwx\\SilverstripeDataManager\\Includes\\SessionMessage %>
    $FilterForm
    <% if $FilterIsSet %>
        <p>
            <a href="$Link" class="uk-button uk-button-default uk-button-small" data-behaviour="clear_filter">Filter
                zurücksetzen</a>
            $Items.Count Einträge gefunden
        </p>
    <% end_if %>
    <div class="uk-overflow-auto">
        <table class="uk-table uk-table-striped">
            <thead>
            <tr>
                <% loop $DataManagerFields %>
                    <th>$Title</th>
                <% end_loop %>
            </tr>
            </thead>
            <tbody>
            <% if $Items.Count > 0 %>
                <% loop $Items %>
                    <tr>
                        <% loop $DataManagerData %>
                            <% if $IsFirst %>
                                <td><a href="$Top.Link("view")/$Up.ID">$Value</a></td>
                            <% else %>
                                <td>$Value</td>
                            <% end_if %>
                        <% end_loop %>
                        <% if $CanEdit %>
                            <td>
                                <a href="$Top.Link("edit")/$ID"
                                   class="uk-icon-button"
                                   title="Bearbeiten"
                                   uk-icon="icon: pencil"></a>
                            </td>
                        <% end_if %>
                        <% if $CanDelete %>
                            <td>
                                <a href="$Top.Link("delete")/$ID?BackURL=$Top.Link"
                                   class="uk-icon-button"
                                   title="Löschen"
                                   onclick="return confirm('Sind Sie sicher?')"
                                   uk-icon="icon: trash"></a>
                            </td>
                        <% end_if %>
                    </tr>
                <% end_loop %>
            <% else %>
                <tr>
                    <td><p>- Keine Einträge gefunden -</p></td>
                </tr>
            <% end_if %>
            </tbody>
        </table>
    </div>
    <% include Atwx\SilverstripeDataManager\Includes\Pagination ItemList=$Items %>
</div>
