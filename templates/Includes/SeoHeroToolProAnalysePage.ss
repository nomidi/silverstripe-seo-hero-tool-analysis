<!DOCTYPE html>
<html lang="$ContentLocale">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><%t SeoHeroToolPro.ANALYSEHEADLINE 'SEO Auswertung' %> $PageLink</title>
  <link rel="stylesheet" href="$SHTProPath/thirdparty/bootstrap-3.3.7-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="$SHTProPath/thirdparty/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">
  <link rel="stylesheet" href="$SHTProPath/css/style.css">
  <script src="$SHTProPath/thirdparty/jquery-3.2.1.min.js" ></script>
  <script src="$SHTProPath/thirdparty/bootstrap-3.3.7-dist/js/bootstrap.min.js" ></script>

</head>
<body>
  <div class="container">
    <div class="row">

        <div class="col-md-12">
          <h1><%t SeoHeroToolPro.ANALYSEHEADLINE 'SEO analysis' %></h1>
          <p><strong><a href="$PageLink" target="_blank">$PageLink</a></strong></p>
          <% if AccessError == '' %>

          <div class="custom-content">
            <table class="table table-bordered">
                <% with CountResults %>
                <% loop UnsortedListEntries %>
                <tr><td>$CountLabel</td><td colspan="2">$CountValue</td></tr>
                <% end_loop %>
                <tr><td><%t SeoHeroToolPro.PublishStatus 'Actual Status' %></td><td>$ActualPublishedStatus</td></tr>
                <% end_with %>
                <tr class="tr-api"><td><%t SeoHeroToolPro.PageSpeed 'PageSpeed' %></td><% if PageSpeedResults %><td><% with PageSpeedResults %><% loop UnsortedListEntries %>$Content<% end_loop %><% end_with %><% if pageSpeedTimeStamp %><br/><%t SeoHeroToolPro.LastCheckInformation 'Last checked at ' %>$pageSpeedTimeStamp<% end_if %></td><% end_if %>
                  <td><% if PageSpeedLink %><a href="$PageSpeedLink" target="_blank"><%t SeoHeroToolPro.LinkToPageSpeedInsights 'Open PageSpeed Insights' %></a><% else %>$PageSpeedMessage<% end_if %></td></tr>
                <tr class="tr-api"><td><%t SeoHeroToolPro.W3CResult 'W3C Result' %></td><% if W3CResults %><td><% with W3CResults %><% loop UnsortedListEntries %>$Content<% end_loop %><% end_with %><% if W3CTimeStamp %><br/><%t SeoHeroToolPro.LastCheckInformation 'Last checked at ' %> $W3CTimeStamp<% end_if %></td>
                  <% end_if %><td><% if W3CLink %><a href="$W3CLink" target="_blank"><%t SeoHeroToolPro.LinkToW3C 'Open W3C Results' %></a><% else %>$W3CMessage<% end_if %></td></tr>
            </table>
          </div>
          <% end_if %>
        </div>
    </div>

    <% if AccessError %>
    <div class="row info-boxes">
      <div class="col-md-12">
        <h2><%t SeoHeroToolPro.ANALYSEGENERALACCESSISSUE 'General Access Issue' %></h2>
      </div>
      <div class="col-md-12">
        <p>$AccessError</p>
      </div>
    </div>
    <% else %>

    <div class="row info-boxes">
      <div class="col-md-12">
        <h2><%t SeoHeroToolPro.ANALYSEGENERAL 'Generel Analysis' %></h2>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-info">
          <div class="inner">
            <h3><%t SeoHeroToolPro.ANALYSETESTS 'Checks' %></h3>
            <p>$RulesTotal</p>
          </div>
          <span class="glyphicon glyphicon-check button-xl" aria-hidden="true"></span>

        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-success">
          <div class="inner">
            <h3><%t SeoHeroToolPro.ANALYSEPASSED 'Passed' %></h3>
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
            <h3><%t SeoHeroToolPro.ANALYSEWARNING 'Notice' %></h3>
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
            <h3><%t SeoHeroToolPro.ANALYSEERROR 'Error' %></h3>
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
            <li role="presentation" class="nav-item active"><a class="nav-link " data-toggle="tab" href="#analyse-home" role="tab"><span class="glyphicon glyphicon-ok " aria-hidden="true"></span> Bestanden</a></li>
            <li role="presentation" class="nav-item"><a class="nav-link" data-toggle="tab" href="#analyse-warning" role="tab"><span class="glyphicon glyphicon-info-sign " aria-hidden="true"></span> Hinweise</a></li>
            <li role="presentation" class="nav-item"><a class="nav-link" data-toggle="tab" href="#analyse-error" role="tab"><span class="glyphicon glyphicon-remove " aria-hidden="true"></span> Fehler</a></li>
          </ul>


        <div class="tab-content">
          <div class="tab-pane active" id="analyse-home" role="tabpanel">
            <% include SeoHeroToolProAnalyse IconMessVal=3 %>
          </div>
          <div class="tab-pane" id="analyse-warning" role="tabpanel">
            <% include SeoHeroToolProAnalyse IconMessVal=2 %>
          </div>
          <div class="tab-pane" id="analyse-error" role="tabpanel">
            <% include SeoHeroToolProAnalyse IconMessVal=1 %>
          </div>
        </div>
      </div>
    </div>
  </div>





    <% if $KeywordRulesTotal == 0 %>
    <div class="row">
      <div class="col-md-12">
      <h2><%t SeoHeroToolPro.ANALYSEKEYWORDHEADLINE 'Keyword Analysis' %></h2>
      </div>
      <div>
      <div class="col-md-12"><p><%t SeoHeroToolPro.NOKEYWORDS 'No keywords entered for this website.' %></p>
      </div>
    </div>
  <% else %>
    <div class="row info-boxes">
      <div class="col-md-12">
        <h2><%t SeoHeroToolPro.ANALYSEKEYWORDHEADLINE 'Keyword Auswertung' %></h2>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-info">
          <div class="inner">
            <h3><%t SeoHeroToolPro.ANALYSETESTS 'Checks' %></h3>
            <p>$KeywordRulesTotal</p>
          </div>
          <span class="glyphicon glyphicon-check button-xl" aria-hidden="true"></span>

        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-success">
          <div class="inner">
            <h3><%t SeoHeroToolPro.ANALYSEPASSED 'Passed' %></h3>
            <p>$KeywordRulesGood</p>
          </div>
          <div class="icon">
            <span class="glyphicon glyphicon-ok button-xl" aria-hidden="true"></span>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-warning">
          <div class="inner">
            <h3><%t SeoHeroToolPro.ANALYSEWARNING 'Notice' %></h3>
            <p>$KeywordRulesNotice</p>
          </div>
          <div class="icon">
              <span class="glyphicon glyphicon-info-sign button-xl" aria-hidden="true"></span>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="alert alert-danger">
          <div class="inner">
            <h3><%t SeoHeroToolPro.ANALYSEERROR 'Error' %></h3>
            <p>$KeywordRulesWrong</p>
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
            <li role="presentation" class="nav-item active"><a class="nav-link " data-toggle="tab" href="#keyword-home" role="tab"><span class="glyphicon glyphicon-ok " aria-hidden="true"></span> Bestanden</a></li>
            <li role="presentation" class="nav-item"><a class="nav-link" data-toggle="tab" href="#keyword-warning" role="tab"><span class="glyphicon glyphicon-info-sign " aria-hidden="true"></span> Hinweise</a></li>
            <li role="presentation" class="nav-item"><a class="nav-link" data-toggle="tab" href="#keyword-error" role="tab"><span class="glyphicon glyphicon-remove " aria-hidden="true"></span> Fehler</a></li>
          </ul>


        <div class="tab-content">
          <div class="tab-pane active" id="keyword-home" role="tabpanel">
            <% include SeoHeroToolProKeywordAnalyse IconMessVal=3 %>
          </div>
          <div class="tab-pane" id="keyword-warning" role="tabpanel">
              <% include SeoHeroToolProKeywordAnalyse IconMessVal=2 %>
          </div>
          <div class="tab-pane" id="keyword-error" role="tabpanel">
              <% include SeoHeroToolProKeywordAnalyse IconMessVal=1 %>
          </div>
        </div>
      </div>
    </div>
  </div>


  <% end_if %>

  </div>
  <% end_if %>
  </div>
</body>
</html>
