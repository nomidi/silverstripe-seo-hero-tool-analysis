<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <link rel="stylesheet" href="$SHTProPath/thirdparty/bootstrap-3.3.7-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="$SHTProPath/thirdparty/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">
  <link rel="stylesheet" href="$SHTProPath/css/style.css">
  <script src="$SHTProPath//thirdparty/jquery-3.2.1.min.js" ></script>
  <script src="$SHTProPath/thirdparty/bootstrap-3.3.7-dist/js/bootstrap.min.js" ></script>

</head>
<body>
  <div class="container">
    <div class="row">

        <div class="col-md-12">
          <h1>SEO Auswertung</h1>
          <p><strong><a href="$PageLink" target="_blank">$PageLink</a></strong></p>
        </div>
    </div>

    <div class="row info-boxes">
      <div class="col-md-12">
        <h2>Allgemeine Auswertung</h2>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-info">
          <div class="inner">
            <h3>Prüfungen</h3>
            <p>$RulesTotal</p>
          </div>
          <span class="glyphicon glyphicon-check button-xl" aria-hidden="true"></span>

        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-success">
          <div class="inner">
            <h3>Bestanden</h3>
            <p>$RulesGood</p>
          </div>
          <div class="icon">
            <span class="glyphicon glyphicon-ok button-xl" aria-hidden="true"></span>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-warning">
          <div class="inner">
            <h3>Hinweise</h3>
            <p>$RulesNotice</p>
          </div>
          <div class="icon">
              <span class="glyphicon glyphicon-info-sign button-xl" aria-hidden="true"></span>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-danger">
          <div class="inner">
            <h3>Fehler</h3>
            <p>$RulesWrong</p>
          </div>
          <div class="icon">
            <span class="glyphicon glyphicon-remove button-xl" aria-hidden="true"></span>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="nav-tabs-custom custom-content ">
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="nav-item active"><a class="nav-link " data-toggle="tab" href="#home" role="tab"><span class="glyphicon glyphicon-ok " aria-hidden="true"></span> Bestanden</a></li>
            <li role="presentation" class="nav-item"><a class="nav-link" data-toggle="tab" href="#warning" role="tab"><span class="glyphicon glyphicon-info-sign " aria-hidden="true"></span> Hinweise</a></li>
            <li role="presentation" class="nav-item"><a class="nav-link" data-toggle="tab" href="#error" role="tab"><span class="glyphicon glyphicon-remove " aria-hidden="true"></span> Fehler</a></li>
          </ul>


        <div class="tab-content">
          <div class="tab-pane active" id="home" role="tabpanel">
            <% include SeoHeroToolProAnalyse_Good %>
          </div>
          <div class="tab-pane" id="warning" role="tabpanel">
            <% include SeoHeroToolProAnalyse_Notice %>
          </div>
          <div class="tab-pane" id="error" role="tabpanel">
            <% include SeoHeroToolProAnalyse_Error %>
          </div>
        </div>
      </div>
    </div>
</div>
<div class="row">

      Keywordprüfungen: $KeywordRulesTotal<br/>
      Falsch: $KeywordRulesWrong<br/>
      Hinweis: $KeywordRulesNotice<br/>
      Gut: $KeywordRulesGood<br/>

      Falsch:
        <% include SeoHeroToolProKeywordAnalyse_Error %>
      Hinweis:
        <% include SeoHeroToolProKeywordAnalyse_Notice %>
      Gut:
        <% include SeoHeroToolProKeywordAnalyse_Good %>
    </div>
  </div>
</body>
</html>
