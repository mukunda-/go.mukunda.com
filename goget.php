<?php
// go.mukunda.com go-import proxy
// Licensed under MIT
require 'config.php';

$path = strtok($_SERVER["REQUEST_URI"], '?');

//----------------------------------------------------------------------------------------
// Debug routing for test-server.js
//----------------------------------------------------------------------------------------
$directPaths = [
   '/favicon.png' => true,
];

if (isset($directPaths[$path])) {
   return false;
}
//----------------------------------------------------------------------------------------

# Default package host
if (!isset($packagehost)) {
   $packagehost = $_SERVER["HTTP_HOST"];
}

// maybe used in future?
function checkGithubRepoExists($url) {
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Accept: application/vnd.github+json",
      "X-GitHub-Api-Version: 2022-11-28"
   ]);

   $response = curl_exec($ch);
   $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   curl_close($ch);

   return $httpcode != 404;
}

//----------------------------------------------------------------------------------------
function getPackageInfo($path) {
   $parent = explode('/', $path)[1];
   $parent = trim($parent);
   if ($parent == "") {
      return false;
   }

   $package = Config::PACKAGE_HOST . "/$parent";

   $repo = Config::PACKAGES[$package] ?? Config::DEFAULT_REPO . "/$parent";

   return [
      "package" => $package,
      "repo"    => $repo,
   ];
}

function render($pinfo) {
   $meta_tags = [];
   $redirect_script = "";

   if ($pinfo) {
      $package = $pinfo["package"];
      $repo    = $pinfo["repo"];
      $goimport_meta = "$package git $repo";
      $gosource_meta = "$package $repo $repo/tree/main{/dir} $repo/tree/main{/dir}/{file}#L{line}";
      $meta_tags[] = "<meta name=\"go-import\" content=\"$goimport_meta\">";
      $meta_tags[] = "<meta name=\"go-source\" content=\"$gosource_meta\">";
      $meta_tags[] = "<meta http-equiv=\"refresh\" content=\"3; url=$repo\">";
   }

   
?><!DOCTYPE html>
<html>
   <head>
      <Title>Let's Go</Title>
      <link rel="icon" type="image/png" href="favicon.png">
      <?php
         echo implode("\n", $meta_tags);
      ?>
      <style>
         img {
            position: fixed;
            left: calc(50% - 8px);
            bottom: -32px;
            image-rendering: pixelated;
            animation: climb 0.5s 2s forwards;
         }

         @keyframes climb {
            0% {
               bottom: -32px;
            }
            100% {
               bottom: 0px;
            }
         }
      </style>
   </head>
   <body>
      <img src="favicon.png" width="32">
   </body>
</html>
<?php
}

render(getPackageInfo($path));
