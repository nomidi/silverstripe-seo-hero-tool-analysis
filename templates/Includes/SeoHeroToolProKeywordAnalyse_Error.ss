<table class="table table-bordered">
    <% with KeywordResults %>
    <% loop KeywordEntries %>
    <% if IconMess == 1 %>
    <tr class="wrong"><td class="area">$Up.Headline</td><td class="content">$Content</td></tr>
    <% end_if %>
    <% end_loop %>
    <% end_with %>


</table>
