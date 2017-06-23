<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <link rel="stylesheet" href="$SHTProPath/css/shtproanalyse.css">
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
    Falsch:
    <% include SeoHeroToolProAnalyse_Error %>

    Hinweis:
    <% include SeoHeroToolProAnalyse_Notice %>

    Gut:
    <% include SeoHeroToolProAnalyse_Good %>
  
  </div>
</body>
</html>
