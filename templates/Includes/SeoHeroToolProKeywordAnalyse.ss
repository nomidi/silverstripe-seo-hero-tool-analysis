<table class="table table-bordered">
    <% with KeywordResults %>
    <% loop KeywordEntries %>
    <% if IconMess == $Top.IconMessVal %>
    <tr><td>$Up.Headline</td><td class="content">$Content</td></tr>
    <% end_if %>
    <% end_loop %>
    <% end_with %>
</table>
