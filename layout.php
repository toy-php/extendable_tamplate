<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $this->title ?></title>
</head>
<body>
<div class="wrapper">
    <div class="content"><?= $this->content ?></div>
    <div class="sidebar"><?= $this->eachBlock($this->sidebar) ?></div>
</div>
</body>
</html>