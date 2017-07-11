<table class="table table-bordered">
    <% with KeywordResults %>
    <% loop KeywordEntries %>
    <% if IconMess == $Top.IconMessVal %>
    <tr><td>$Up.Headline</td><td class="content">$Content</td><% if Top.DebugMode %><td><a href="$Top.LinkToWebsite{$HelpLink}" title="<%t SeoHeroToolPro.HelpTitle 'More Information on' %> $HelpLink" target="_blank"><%t SeoHeroToolPro.Help 'Help' %></a></td><% end_if %></tr>
    <% end_if %>
    <% end_loop %>
    <% end_with %>
</table>
