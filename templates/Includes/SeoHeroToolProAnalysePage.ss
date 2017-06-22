<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<body>
  <div class="wrapper">
    <div>
      <a href="$PageLink" target="_blank">Link zur Seite</a>
    </div>
    <div>
      Regeln: $RulesTotal<br/>
      Falsch: $RulesWrong<br/>
      Hinweis: $RulesNotice<br/>
      Gut: $RulesGood<br/>
    </div>
    <div>
      <% with TitleResults %>
        $Headline:<br/>
        <% loop UnsortedListEntries %>
          $Content<br/>
        <% end_loop %>
      <% end_with %>
    </div>
    <div>
      <% with MetaResults %>
      $Headline:<br/>
        <% loop UnsortedListEntries %>
          $Content<br/>
        <% end_loop %>
      <% end_with %>
    </div>
    <div>
      <% with URLResults %>
      $Headline:<br/>
        <% loop UnsortedListEntries %>
          $Content<br/>
        <% end_loop %>
      <% end_with %>
    </div>
    <div>
      <% with WordCountResults %>
      $Headline:<br/>
        <% loop UnsortedListEntries %>
          $Content<br/>
        <% end_loop %>
      <% end_with %>
    </div>
    <div>
      <% with DirectoryDepthResults %>
      $Headline:<br/>
        <% loop UnsortedListEntries %>
          $Content<br/>
        <% end_loop %>
      <% end_with %>
    </div>
    <div>
      <% with HeadlineResults %>
      $Headline:<br/>
        <% loop UnsortedListEntries %>
          $Content<br/>
        <% end_loop %>
      <% end_with %>
    </div>
    <div>
      <% with LinkResults %>
      $Headline:<br/>
        <% loop UnsortedListEntries %>
          $Content<br/>
        <% end_loop %>
      <% end_with %>
    </div>
  </div>
</body>
</html>
