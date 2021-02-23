<?php

/**
 * Template args from other included templates or from controller
 * @var string[]
 */
$tpl = $GLOBALS['tpl'];

$defaultArgs = [
    'title' => 'Hyphenator page'
];

foreach ($defaultArgs as $key => $value) {
    if (!array_key_exists($key, $tpl)) {
        $tpl[$key] = $value;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $tpl['title'] ?></title>
</head>
<body>
    <?php include __DIR__ . '/Common/navbar.php' ?>
    <?php echo $tpl['body'] ?>
</body>
</html>