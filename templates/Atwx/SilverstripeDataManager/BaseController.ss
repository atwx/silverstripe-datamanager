<!doctype html>
<html lang="de">
<head>
    <% base_tag %>
    $MetaTags(false)
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta charset="utf-8">
    <title>$Title - $SiteConfig.Title</title>
    $Vite.HeaderTags.RAW
<%--    TODO --%>
    <link rel="stylesheet" href="http://localhost:3000/app/client/src/scss/main.scss">
</head>
<body>
<% include Header %>
    <% include Sidebar %>
    <main class="main">
        $Layout
    </main>
$Vite.BodyTags.RAW
</body>
</html>