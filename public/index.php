<?php
function mix($file_puth)
{
    if (!file_exists("mix-manifest.json")) {
        return $file_puth;
    }
    $mixManifest = file_get_contents("mix-manifest.json");
    $mixManifest = json_decode($mixManifest);
    if (isset($mixManifest->$file_puth)) {
        return $mixManifest->$file_puth;
    }
    return $file_puth;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Styles -->
    <link href="<?php echo mix('/dist/css/backend/app.css'); ?>" rel="stylesheet">
    <title>Hyperf Dashboard</title>

    <!-- Fonts -->

    <!-- Styles -->
    <style>

    </style>
</head>
<body>
<div id="app"></div>
<script src="<?php echo mix('/dist/js/backend/app.js'); ?>"></script>
</body>
</html>
